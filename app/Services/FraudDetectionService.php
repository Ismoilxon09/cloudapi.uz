<?php

namespace App\Services;

use App\Models\BlockedIp;
use App\Models\SignupAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Multi-account fraud detection
 *
 * Tekshiruvlar:
 * 1. IP bloklangan (umuman saytni ko'ra olmaydi)
 * 2. IP duplicate (oxirgi 24 soatda akkaunt bor) → bonus = 0
 * 3. Device fingerprint duplicate → bonus = 0
 * 4. VPN/Proxy → bonus = 0
 * 5. Disposable email → bonus = 0
 * 6. Brute-force (5+ urinish) → IP blok
 */
class FraudDetectionService
{
    // Konfiguratsiya
    private const IP_DUPLICATE_HOURS = 24;       // 1 IP dan 24 soatda max 1 ta bonus
    private const MAX_ATTEMPTS_PER_HOUR = 5;     // 1 soatda max 5 urinish
    private const BLOCK_DURATION_HOURS = 24;     // IP blok davomiyligi

    // Disposable email domenlar (eng mashhurlari)
    private const DISPOSABLE_DOMAINS = [
        '10minutemail.com', '10minutemail.net', 'tempmail.com', 'temp-mail.org',
        'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org',
        'mailinator.com', 'mailinator.net', 'maildrop.cc',
        'throwaway.email', 'getnada.com', 'yopmail.com', 'fakeinbox.com',
        'sharklasers.com', 'spam4.me', 'mohmal.com', 'trbvm.com',
        'tempinbox.com', 'mail-temp.com', 'dispostable.com',
        'tempmailaddress.com', 'tempr.email', 'wegwerfmail.de',
        '0-mail.com', 'emailondeck.com', 'jetable.org',
    ];

    /**
     * Asosiy tekshirish — ro'yxatdan o'tish jarayonida chaqiriladi.
     *
     * @return array {
     *   ok: bool,                          // ro'yxat ruxsat etiladimi
     *   blocked: bool,                     // umuman blok (akkaunt yaratilmaydi)
     *   bonus_eligible: bool,              // bonus berish mumkinmi
     *   bonus_amount: int,                 // berilishi mumkin bonus miqdori
     *   is_suspicious: bool,               // shubhali flag
     *   fraud_score: int,                  // 0-100 shubhalilik darajasi
     *   reason: string|null,               // user'ga ko'rsatiladigan sabab
     *   reason_internal: string|null,      // admin uchun ichki sabab
     *   country: string|null,
     *   is_vpn: bool,
     * }
     */
    public function check(string $ip, string $email, ?string $deviceHash, ?string $userAgent, int $bonusAmount = 5000): array
    {
        $result = [
            'ok' => true,
            'blocked' => false,
            'bonus_eligible' => true,
            'bonus_amount' => $bonusAmount,
            'is_suspicious' => false,
            'fraud_score' => 0,
            'reason' => null,
            'reason_internal' => null,
            'country' => null,
            'is_vpn' => false,
        ];

        // ====================================================
        // 1. IP BLOKLANGANMI?
        // ====================================================
        if ($this->isIpBlocked($ip)) {
            $result['ok'] = false;
            $result['blocked'] = true;
            $result['bonus_eligible'] = false;
            $result['bonus_amount'] = 0;
            $result['fraud_score'] = 100;
            $result['reason'] = "Sizning IP manzilingiz bloklangan. Yordam uchun support'ga murojaat qiling.";
            $result['reason_internal'] = "IP {$ip} is in blocked_ips table";
            return $result;
        }

        // ====================================================
        // 2. BRUTE-FORCE: 1 soatda ko'p urinish
        // ====================================================
        $recentAttempts = SignupAttempt::where('ip_address', $ip)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentAttempts >= self::MAX_ATTEMPTS_PER_HOUR) {
            // Auto blok 24 soatga
            $this->blockIp($ip, "Brute-force: {$recentAttempts} attempts in 1 hour", self::BLOCK_DURATION_HOURS);

            $result['ok'] = false;
            $result['blocked'] = true;
            $result['bonus_eligible'] = false;
            $result['bonus_amount'] = 0;
            $result['fraud_score'] = 100;
            $result['reason'] = "Juda ko'p urinish. IP manzilingiz 24 soatga bloklandi.";
            $result['reason_internal'] = "Brute-force: {$recentAttempts} attempts";
            return $result;
        }

        // ====================================================
        // 3. DISPOSABLE EMAIL
        // ====================================================
        if ($this->isDisposableEmail($email)) {
            $result['bonus_eligible'] = false;
            $result['bonus_amount'] = 0;
            $result['is_suspicious'] = true;
            $result['fraud_score'] += 40;
            $result['reason'] = "Bir martalik email manzili aniqlandi. Bonus berilmadi. Doimiy email bilan qaytadan urinib ko'ring.";
            $result['reason_internal'] = "Disposable email: " . substr(strrchr($email, '@'), 1);
            // Davom etamiz — akkaunt yaratiladi, faqat bonus yo'q
        }

        // ====================================================
        // 4. IP DUPLICATE (oxirgi 24 soatda akkaunt yaratilgan)
        // ====================================================
        $sameIpUsers = User::where('signup_ip', $ip)
            ->where('created_at', '>=', now()->subHours(self::IP_DUPLICATE_HOURS))
            ->count();

        if ($sameIpUsers > 0) {
            $result['bonus_eligible'] = false;
            $result['bonus_amount'] = 0;
            $result['is_suspicious'] = true;
            $result['fraud_score'] += 50;
            $result['reason'] = "Bu IP manzildan oxirgi 24 soat ichida akkaunt yaratilgan. Yangi bonus berilmadi.";
            $result['reason_internal'] = "Duplicate IP: {$sameIpUsers} accounts in last 24h";
        }

        // ====================================================
        // 5. DEVICE FINGERPRINT DUPLICATE
        // ====================================================
        if ($deviceHash) {
            $sameDeviceUsers = User::where('signup_device_hash', $deviceHash)
                ->where('created_at', '>=', now()->subHours(self::IP_DUPLICATE_HOURS))
                ->count();

            if ($sameDeviceUsers > 0) {
                $result['bonus_eligible'] = false;
                $result['bonus_amount'] = 0;
                $result['is_suspicious'] = true;
                $result['fraud_score'] += 50;
                $result['reason'] = $result['reason'] ?? "Bu qurilmadan oxirgi 24 soat ichida akkaunt yaratilgan. Yangi bonus berilmadi.";
                $result['reason_internal'] = ($result['reason_internal'] ?? '') . " | Duplicate device: {$sameDeviceUsers}";
            }
        } else {
            // Device hash kelmagan — fingerprint JS ishlamadi
            $result['fraud_score'] += 10;
        }

        // ====================================================
        // 6. VPN / PROXY (ixtiyoriy, ipapi.co bepul)
        // ====================================================
        $ipInfo = $this->checkVpnAndCountry($ip);
        if ($ipInfo) {
            $result['country'] = $ipInfo['country'] ?? null;
            $result['is_vpn'] = $ipInfo['is_vpn'] ?? false;

            if ($result['is_vpn']) {
                $result['bonus_eligible'] = false;
                $result['bonus_amount'] = 0;
                $result['is_suspicious'] = true;
                $result['fraud_score'] += 40;
                $result['reason'] = $result['reason'] ?? "VPN/Proxy aniqlandi. Bonus VPN orqali berilmaydi.";
                $result['reason_internal'] = ($result['reason_internal'] ?? '') . " | VPN detected";
            }
        }

        return $result;
    }

    /**
     * IP bloklanganmi?
     */
    public function isIpBlocked(string $ip): bool
    {
        return Cache::remember("blocked_ip:{$ip}", 60, function () use ($ip) {
            $block = BlockedIp::where('ip_address', $ip)->first();
            if (!$block) return false;

            // Permanent blok
            if ($block->is_permanent) return true;

            // Vaqtinchalik blok — vaqt o'tganmi?
            if ($block->blocked_until && $block->blocked_until->isPast()) {
                $block->delete();
                return false;
            }

            return true;
        });
    }

    /**
     * IP'ni blok qilish
     */
    public function blockIp(string $ip, string $reason, int $hours = 24, bool $permanent = false): void
    {
        BlockedIp::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'blocked_until' => $permanent ? null : now()->addHours($hours),
                'is_permanent' => $permanent,
                'attempts_count' => DB::raw('attempts_count + 1'),
            ]
        );

        Cache::forget("blocked_ip:{$ip}");

        Log::channel('security')->warning("IP blocked: {$ip}", [
            'reason' => $reason,
            'hours' => $hours,
            'permanent' => $permanent,
        ]);
    }

    /**
     * Disposable email tekshirish
     */
    public function isDisposableEmail(string $email): bool
    {
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return in_array($domain, self::DISPOSABLE_DOMAINS);
    }

    /**
     * VPN va davlat aniqlash — ipapi.co (bepul, soatlik 1000 so'rov)
     */
    private function checkVpnAndCountry(string $ip): ?array
    {
        // Local IP — tekshirish kerakmas
        if (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return ['country' => 'LOCAL', 'is_vpn' => false];
        }

        return Cache::remember("ip_info:{$ip}", 3600, function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("https://ipapi.co/{$ip}/json/");

                if ($response->successful()) {
                    $data = $response->json();

                    // ipapi.co'da VPN aniqlash uchun "threat" yoki "abuse" mavjud emas,
                    // lekin organization name'da "VPN", "Proxy", "Hosting" bo'lsa shubhali
                    $org = strtolower($data['org'] ?? '');
                    $isVpn = str_contains($org, 'vpn') 
                          || str_contains($org, 'proxy') 
                          || str_contains($org, 'hosting')
                          || str_contains($org, 'datacenter');

                    return [
                        'country' => $data['country_code'] ?? null,
                        'is_vpn' => $isVpn,
                        'org' => $data['org'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("VPN check failed for {$ip}: " . $e->getMessage());
            }
            return null;
        });
    }

    /**
     * Signup attempt'ni saqlash
     */
    public function logAttempt(array $data): SignupAttempt
    {
        return SignupAttempt::create($data);
    }
}
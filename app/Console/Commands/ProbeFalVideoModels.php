<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * fal.ai video model ID'lari haqiqiyligini tekshiradi.
 * Bo'sh input yuboradi: model ID noto'g'ri bo'lsa "not a valid model ID",
 * to'g'ri bo'lsa validation xatosi (prompt kerak) — ikkalasi ham PULSIZ
 * (job navbatga tushmaydi). Agar navbatga tushsa, darrov bekor qilinadi.
 *
 *   php artisan video:probe-fal
 */
class ProbeFalVideoModels extends Command
{
    protected $signature = 'video:probe-fal';
    protected $description = 'fal.ai video model ID lari haqiqiyligini tekshiradi (pulsiz)';

    public function handle(): int
    {
        $key = config('services.fal.key');
        if (!$key) {
            $this->error('FAL_KEY sozlanmagan (.env). Avval kalitni qo\'shing.');
            return self::FAILURE;
        }
        $base = rtrim(config('services.fal.base_url', 'https://queue.fal.run'), '/');

        $candidates = [
            'fal-ai/minimax/video-01',
            'fal-ai/minimax/hailuo-02/standard/text-to-video',
            'fal-ai/minimax/hailuo-02/pro/text-to-video',
            'fal-ai/kling-video/v1.6/standard/text-to-video',
            'fal-ai/kling-video/v2/master/text-to-video',
            'fal-ai/kling-video/v2.1/master/text-to-video',
            'fal-ai/kling-video/v2.5-turbo/pro/text-to-video',
            'fal-ai/luma-dream-machine',
            'fal-ai/luma-dream-machine/ray-2',
            'fal-ai/veo2',
            'fal-ai/veo3',
            'fal-ai/veo3/fast',
            'fal-ai/wan/v2.2-a14b/text-to-video',
            'fal-ai/wan-t2v',
            'fal-ai/pika/v2.2/text-to-video',
            'fal-ai/hunyuan-video',
            'fal-ai/ltx-video',
            'fal-ai/ltxv-13b-098-distilled',
        ];

        $valid = [];
        $this->info('fal.ai video modellarini tekshirish...');
        $this->newLine();

        foreach ($candidates as $id) {
            try {
                $resp = Http::withHeaders(['Authorization' => "Key {$key}"])
                    ->timeout(20)->post("{$base}/{$id}", []);
                $body = $resp->body();
                $status = $resp->status();

                if (stripos($body, 'not a valid model') !== false) {
                    $this->line("  <fg=red>✗ INVALID</>  {$id}");
                } elseif ($status === 200 && ($resp->json()['request_id'] ?? null)) {
                    $rid = $resp->json()['request_id'];
                    try {
                        Http::withHeaders(['Authorization' => "Key {$key}"])
                            ->put("{$base}/{$id}/requests/{$rid}/cancel");
                    } catch (\Throwable $e) {
                    }
                    $this->line("  <fg=green>✓ VALID</>    {$id}  (navbat bekor qilindi)");
                    $valid[] = $id;
                } else {
                    // validation xatosi (422) → model mavjud, faqat input kerak
                    $this->line("  <fg=green>✓ VALID</>    {$id}  (HTTP {$status})");
                    $valid[] = $id;
                }
            } catch (\Throwable $e) {
                $this->line("  <fg=yellow>? ERROR</>    {$id}  " . substr($e->getMessage(), 0, 60));
            }
            usleep(200000); // 0.2s
        }

        $this->newLine();
        $this->info('=== HAQIQIY (VALID) MODEL ID lar ===');
        foreach ($valid as $v) {
            $this->line("  {$v}");
        }
        $this->newLine();
        $this->comment('Shu ro\'yxatni yuboring — men aynan shularni katalogga qo\'shaman.');

        return self::SUCCESS;
    }
}

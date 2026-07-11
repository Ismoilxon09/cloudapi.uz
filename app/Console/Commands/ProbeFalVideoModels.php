<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * fal.ai video model ID'lari haqiqiyligini tekshiradi — fal OpenAPI sxemasi orqali.
 * Job YUBORMAYDI (pulsiz): model mavjud bo'lsa sxema qaytadi, bo'lmasa xato.
 *
 *   php artisan video:probe-fal
 */
class ProbeFalVideoModels extends Command
{
    protected $signature = 'video:probe-fal';
    protected $description = 'fal.ai video model ID lari haqiqiyligini tekshiradi (pulsiz, OpenAPI orqali)';

    public function handle(): int
    {
        $key = config('services.fal.key');
        if (!$key) {
            $this->error("FAL_KEY sozlanmagan (.env).");
            return self::FAILURE;
        }

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
            'fal-ai/pika/v2.2/text-to-video',
            'fal-ai/hunyuan-video',
            'fal-ai/ltx-video',
        ];

        $this->info('fal.ai modellarni OpenAPI sxemasi orqali tekshirish (job yuborilmaydi)...');
        $this->newLine();

        $valid = [];
        foreach ($candidates as $id) {
            try {
                $r = Http::withHeaders(['Authorization' => "Key {$key}"])
                    ->timeout(15)
                    ->get('https://fal.ai/api/openapi/queue/openapi.json', ['endpoint_id' => $id]);

                $body = $r->body();
                $isSchema = $r->successful()
                    && (str_contains($body, '"openapi"') || str_contains($body, '"paths"'))
                    && stripos($body, 'not a valid') === false;

                if ($isSchema) {
                    $this->line("  <fg=green>✓ VALID</>    {$id}");
                    $valid[] = $id;
                } else {
                    $this->line("  <fg=red>✗ INVALID</>  {$id}  (HTTP {$r->status()})");
                }
            } catch (\Throwable $e) {
                $this->line("  <fg=yellow>? ERROR</>    {$id}  " . substr($e->getMessage(), 0, 50));
            }
            usleep(150000);
        }

        $this->newLine();
        $this->info('=== HAQIQIY (VALID) MODEL ID lar ===');
        foreach ($valid as $v) {
            $this->line("  {$v}");
        }
        $this->newLine();
        $this->comment("Shu ro'yxatni menga yuboring — aynan shularni katalogga qo'shaman.");

        return self::SUCCESS;
    }
}

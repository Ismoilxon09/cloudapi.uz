<?php

namespace Database\Seeders;

use App\Models\AiModel;
use Illuminate\Database\Seeder;

/**
 * Video generatsiya modellari (fal.ai + Replicate).
 * Bular OpenRouter'da yo'q, shuning uchun qo'lda qo'shiladi.
 * OpenRouter sync bularga tegmaydi (model_id'lar OR ro'yxatida yo'q).
 */
class VideoModelsSeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            // ---- fal.ai ----
            [
                'model_id' => 'fal-ai/minimax/video-01',
                'display_name' => 'MiniMax Hailuo · Video',
                'provider' => 'fal',
                'price_usd' => 0.50,
                'description' => 'Matndan video (fal.ai, MiniMax Hailuo).',
            ],
            [
                'model_id' => 'fal-ai/kling-video/v1.6/standard/text-to-video',
                'display_name' => 'Kling 1.6 · Video',
                'provider' => 'fal',
                'price_usd' => 0.35,
                'description' => 'Matndan video (fal.ai, Kling 1.6).',
            ],
            [
                'model_id' => 'fal-ai/luma-dream-machine',
                'display_name' => 'Luma Dream Machine · Video',
                'provider' => 'fal',
                'price_usd' => 0.50,
                'description' => 'Matndan video (fal.ai, Luma).',
            ],

            // ---- Replicate ----
            [
                'model_id' => 'minimax/video-01',
                'display_name' => 'MiniMax Video-01 (Replicate)',
                'provider' => 'replicate',
                'price_usd' => 0.50,
                'description' => 'Matndan video (Replicate, MiniMax).',
            ],
            [
                'model_id' => 'tencent/hunyuan-video',
                'display_name' => 'Hunyuan Video (Replicate)',
                'provider' => 'replicate',
                'price_usd' => 0.40,
                'description' => 'Matndan video (Replicate, Tencent Hunyuan).',
            ],
        ];

        foreach ($models as $m) {
            AiModel::updateOrCreate(
                ['model_id' => $m['model_id']],
                [
                    'slug' => str_replace('/', '-', $m['model_id']),
                    'display_name' => $m['display_name'],
                    'provider' => $m['provider'],
                    'category' => 'video',
                    'description' => $m['description'],
                    'cost_input_usd' => 0,
                    'cost_output_usd' => 0,
                    'margin_percent' => 30,
                    'usd_to_uzs' => 12700,
                    'is_free' => false,
                    'is_featured' => true,
                    'supports_streaming' => false,
                    'active' => true,
                    'metadata' => ['price_usd' => $m['price_usd'], 'kind' => 'video'],
                ]
            );
        }

        $this->command?->info('Video models seeded: ' . count($models));
    }
}

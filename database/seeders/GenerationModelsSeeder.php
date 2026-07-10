<?php

namespace Database\Seeders;

use App\Models\AiModel;
use Illuminate\Database\Seeder;

/**
 * Generatsiya modellarini ANIQ (deterministik) kategoriyalaydi — OpenRouter
 * API javobiga (output_modalities) bog'liq emas. VPS'da bitta buyruq bilan
 * image / audio kategoriyalarini to'g'rilaydi va video modellarni qo'shadi.
 *
 *   php artisan db:seed --class=GenerationModelsSeeder --force
 */
class GenerationModelsSeeder extends Seeder
{
    public function run(): void
    {
        // ---- IMAGE (rasm generatsiya) — aniq ro'yxat ----
        $image = [
            'google/gemini-2.5-flash-image',
            'google/gemini-3-pro-image',
            'google/gemini-3-pro-image-preview',
            'google/gemini-3.1-flash-image',
            'google/gemini-3.1-flash-image-preview',
            'google/gemini-3.1-flash-lite-image',
            'openai/gpt-5-image',
            'openai/gpt-5-image-mini',
            'openai/gpt-5.4-image-2',
        ];
        AiModel::whereIn('model_id', $image)->update(['category' => 'image']);

        // Pattern (kelajakdagi yangi rasm modellari uchun, ehtiyotkor)
        AiModel::where('active', 1)->where('category', '!=', 'image')
            ->where(function ($q) {
                $q->where('model_id', 'like', '%-image')
                  ->orWhere('model_id', 'like', '%-image-preview')
                  ->orWhere('model_id', 'like', '%/gpt-image%');
            })
            ->update(['category' => 'image']);

        // ---- AUDIO (musiqa/nutq generatsiya) ----
        $audio = [
            'google/lyria-3-pro-preview',
            'google/lyria-3-clip-preview',
            'openai/gpt-audio',
            'openai/gpt-audio-mini',
        ];
        AiModel::whereIn('model_id', $audio)->update(['category' => 'audio']);

        AiModel::where('active', 1)->where('category', '!=', 'audio')
            ->where(function ($q) {
                $q->where('model_id', 'like', '%lyria%')
                  ->orWhere('model_id', 'like', '%gpt-audio%');
            })
            ->update(['category' => 'audio']);

        // ---- VIDEO (fal.ai + Replicate) — OpenRouter'da yo'q, qo'lda qo'shiladi ----
        $video = [
            ['model_id' => 'fal-ai/minimax/video-01', 'display_name' => 'MiniMax Hailuo · Video', 'provider' => 'fal', 'price_usd' => 0.50, 'image_model_id' => 'fal-ai/minimax/video-01/image-to-video'],
            ['model_id' => 'fal-ai/kling-video/v1.6/standard/text-to-video', 'display_name' => 'Kling 1.6 · Video', 'provider' => 'fal', 'price_usd' => 0.35, 'image_model_id' => 'fal-ai/kling-video/v1.6/standard/image-to-video'],
            ['model_id' => 'fal-ai/luma-dream-machine', 'display_name' => 'Luma Dream Machine · Video', 'provider' => 'fal', 'price_usd' => 0.50],
            ['model_id' => 'minimax/video-01', 'display_name' => 'MiniMax Video-01 (Replicate)', 'provider' => 'replicate', 'price_usd' => 0.50],
            ['model_id' => 'tencent/hunyuan-video', 'display_name' => 'Hunyuan Video (Replicate)', 'provider' => 'replicate', 'price_usd' => 0.40],
        ];
        foreach ($video as $m) {
            $meta = ['price_usd' => $m['price_usd'], 'kind' => 'video'];
            if (!empty($m['image_model_id'])) $meta['image_model_id'] = $m['image_model_id'];

            AiModel::updateOrCreate(
                ['model_id' => $m['model_id']],
                [
                    'slug' => str_replace('/', '-', $m['model_id']),
                    'display_name' => $m['display_name'],
                    'provider' => $m['provider'],
                    'category' => 'video',
                    'description' => 'Matndan/rasmdan video generatsiya (' . $m['provider'] . ').',
                    'cost_input_usd' => 0,
                    'cost_output_usd' => 0,
                    'margin_percent' => 30,
                    'usd_to_uzs' => 12700,
                    'is_free' => false,
                    'is_featured' => true,
                    'supports_streaming' => false,
                    'active' => true,
                    'metadata' => $meta,
                ]
            );
        }

        // ---- Natijani ko'rsatish (diagnostika) ----
        foreach (['image', 'audio', 'video'] as $cat) {
            $count = AiModel::where('active', 1)->where('category', $cat)->count();
            $this->command?->info("  {$cat}: {$count} model");
        }
        $this->command?->info('Generation models seeded/categorized.');
    }
}

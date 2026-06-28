<?php

namespace Database\Seeders;

use App\Models\AiModel;
use Illuminate\Database\Seeder;

class AiModelSeeder extends Seeder {
    public function run(): void {
        $models = [
            // ===== OpenAI =====
            [
                'model_id' => 'openai/gpt-4o',
                'display_name' => 'GPT-4o',
                'category' => 'chat',
                'description' => 'OpenAI flagship multimodal model',
                'cost_input_usd' => 2.50,
                'cost_output_usd' => 10.00,
                'margin_percent' => 30,
                'context_length' => 128000,
                'capabilities' => ['vision', 'tools', 'json_mode'],
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'model_id' => 'openai/gpt-4o-mini',
                'display_name' => 'GPT-4o Mini',
                'category' => 'chat',
                'description' => 'Arzon va tez GPT-4',
                'cost_input_usd' => 0.15,
                'cost_output_usd' => 0.60,
                'margin_percent' => 40,
                'context_length' => 128000,
                'capabilities' => ['vision', 'tools'],
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'model_id' => 'openai/o1-preview',
                'display_name' => 'o1 Preview',
                'category' => 'reasoning',
                'description' => 'Murakkab mantiqiy fikrlash',
                'cost_input_usd' => 15.00,
                'cost_output_usd' => 60.00,
                'margin_percent' => 25,
                'context_length' => 128000,
                'capabilities' => ['reasoning'],
                'sort_order' => 3,
            ],
            [
                'model_id' => 'openai/o1-mini',
                'display_name' => 'o1 Mini',
                'category' => 'reasoning',
                'description' => 'Tez fikrlash modeli',
                'cost_input_usd' => 3.00,
                'cost_output_usd' => 12.00,
                'margin_percent' => 30,
                'context_length' => 128000,
                'capabilities' => ['reasoning'],
                'sort_order' => 4,
            ],

            // ===== Anthropic =====
            [
                'model_id' => 'anthropic/claude-3.5-sonnet',
                'display_name' => 'Claude 3.5 Sonnet',
                'category' => 'chat',
                'description' => 'Anthropic flagship model',
                'cost_input_usd' => 3.00,
                'cost_output_usd' => 15.00,
                'margin_percent' => 30,
                'context_length' => 200000,
                'capabilities' => ['vision', 'tools', 'artifacts'],
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'model_id' => 'anthropic/claude-3.5-haiku',
                'display_name' => 'Claude 3.5 Haiku',
                'category' => 'chat',
                'description' => 'Tez va arzon Claude',
                'cost_input_usd' => 0.80,
                'cost_output_usd' => 4.00,
                'margin_percent' => 35,
                'context_length' => 200000,
                'capabilities' => ['tools'],
                'sort_order' => 6,
            ],
            [
                'model_id' => 'anthropic/claude-3-opus',
                'display_name' => 'Claude 3 Opus',
                'category' => 'chat',
                'description' => 'Eng kuchli Claude (yuqori sifat)',
                'cost_input_usd' => 15.00,
                'cost_output_usd' => 75.00,
                'margin_percent' => 25,
                'context_length' => 200000,
                'capabilities' => ['vision', 'tools'],
                'sort_order' => 7,
            ],

            // ===== Google =====
            [
                'model_id' => 'google/gemini-pro-1.5',
                'display_name' => 'Gemini Pro 1.5',
                'category' => 'chat',
                'description' => 'Google multimodal AI',
                'cost_input_usd' => 1.25,
                'cost_output_usd' => 5.00,
                'margin_percent' => 30,
                'context_length' => 2000000,
                'capabilities' => ['vision', 'tools'],
                'is_featured' => true,
                'sort_order' => 8,
            ],
            [
                'model_id' => 'google/gemini-2.0-flash-exp:free',
                'display_name' => 'Gemini 2.0 Flash (Bepul)',
                'category' => 'chat',
                'description' => 'Bepul Google modeli',
                'cost_input_usd' => 0,
                'cost_output_usd' => 0,
                'margin_percent' => 100,
                'context_length' => 1000000,
                'capabilities' => ['vision'],
                'is_free' => true,
                'sort_order' => 9,
            ],

            // ===== Meta Llama =====
            [
                'model_id' => 'meta-llama/llama-3.3-70b-instruct',
                'display_name' => 'Llama 3.3 70B',
                'category' => 'chat',
                'description' => 'Open source kuchli model',
                'cost_input_usd' => 0.40,
                'cost_output_usd' => 0.40,
                'margin_percent' => 40,
                'context_length' => 131000,
                'capabilities' => ['tools'],
                'sort_order' => 10,
            ],
            [
                'model_id' => 'meta-llama/llama-3.3-70b-instruct:free',
                'display_name' => 'Llama 3.3 (Bepul)',
                'category' => 'chat',
                'description' => 'Bepul Llama 70B',
                'cost_input_usd' => 0,
                'cost_output_usd' => 0,
                'margin_percent' => 100,
                'context_length' => 8000,
                'is_free' => true,
                'sort_order' => 11,
            ],

            // ===== DeepSeek =====
            [
                'model_id' => 'deepseek/deepseek-chat',
                'display_name' => 'DeepSeek V3',
                'category' => 'chat',
                'description' => 'Arzon va kuchli model',
                'cost_input_usd' => 0.14,
                'cost_output_usd' => 0.28,
                'margin_percent' => 50,
                'context_length' => 64000,
                'capabilities' => ['tools'],
                'is_featured' => true,
                'sort_order' => 12,
            ],
            [
                'model_id' => 'deepseek/deepseek-r1',
                'display_name' => 'DeepSeek R1',
                'category' => 'reasoning',
                'description' => 'Mantiqiy fikrlash (arzon)',
                'cost_input_usd' => 0.55,
                'cost_output_usd' => 2.19,
                'margin_percent' => 30,
                'context_length' => 64000,
                'capabilities' => ['reasoning'],
                'is_featured' => true,
                'sort_order' => 13,
            ],
            [
                'model_id' => 'deepseek/deepseek-chat:free',
                'display_name' => 'DeepSeek V3 (Bepul)',
                'category' => 'chat',
                'description' => 'Bepul DeepSeek',
                'cost_input_usd' => 0,
                'cost_output_usd' => 0,
                'margin_percent' => 100,
                'context_length' => 8000,
                'is_free' => true,
                'sort_order' => 14,
            ],

            // ===== Qwen =====
            [
                'model_id' => 'qwen/qwen-2.5-72b-instruct',
                'display_name' => 'Qwen 2.5 72B',
                'category' => 'chat',
                'description' => 'Alibaba AI model',
                'cost_input_usd' => 0.40,
                'cost_output_usd' => 0.40,
                'margin_percent' => 40,
                'context_length' => 131000,
                'sort_order' => 15,
            ],

            // ===== Mistral =====
            [
                'model_id' => 'mistralai/mistral-large',
                'display_name' => 'Mistral Large',
                'category' => 'chat',
                'description' => 'Frantsuz AI model',
                'cost_input_usd' => 2.00,
                'cost_output_usd' => 6.00,
                'margin_percent' => 30,
                'context_length' => 128000,
                'capabilities' => ['tools'],
                'sort_order' => 16,
            ],

            // ===== Embeddings =====
            [
                'model_id' => 'openai/text-embedding-3-large',
                'display_name' => 'OpenAI Embedding Large',
                'category' => 'embedding',
                'description' => 'Vektor embedding',
                'cost_input_usd' => 0.13,
                'cost_output_usd' => 0,
                'margin_percent' => 40,
                'sort_order' => 20,
            ],
            [
                'model_id' => 'openai/text-embedding-3-small',
                'display_name' => 'OpenAI Embedding Small',
                'category' => 'embedding',
                'description' => 'Arzon embedding',
                'cost_input_usd' => 0.02,
                'cost_output_usd' => 0,
                'margin_percent' => 50,
                'sort_order' => 21,
            ],
        ];

        foreach ($models as $m) {
            AiModel::updateOrCreate(
                ['model_id' => $m['model_id']],
                array_merge($m, [
                    'provider' => 'openrouter',
                    'usd_to_uzs' => 12700,
                    'active' => true,
                ])
            );
        }

        $this->command->info('AI models seeded: ' . count($models));
    }
}
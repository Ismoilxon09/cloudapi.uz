<?php

namespace App\Services\Groq;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY', '');
        $this->baseUrl = 'https://api.groq.com/openai/v1';
    }

    /**
     * Groq sozlanganmi tekshirish
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Oddiy so'rov
     */
    public function sendRequest(string $model, array $body): array
    {
        $body['model'] = $model;
        $body['stream'] = false;

        $response = Http::withHeaders($this->headers())
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", $body);

        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? $response->body() ?: 'Groq request failed';

            // Groq rate limit
            if ($response->status() === 429) {
                throw new \Exception("Groq rate limit. Boshqa modelga o'tilmoqda...", 429);
            }

            throw new \Exception($errorMessage, $response->status());
        }

        return $response->json();
    }

    /**
     * Streaming so'rov (SSE)
     */
    public function streamRequest(string $model, array $body, callable $onChunk): array
    {
        $body['model'] = $model;
        $body['stream'] = true;
        $body['stream_options'] = ['include_usage' => true];

        $client = new Client(['timeout' => 300]);

        $totalTokensIn = 0;
        $totalTokensOut = 0;
        $fullContent = '';

        try {
            $response = $client->post("{$this->baseUrl}/chat/completions", [
                'headers' => $this->headers(),
                'json' => $body,
                'stream' => true,
            ]);

            $stream = $response->getBody();
            $buffer = '';

            while (!$stream->eof()) {
                $chunk = $stream->read(1024);
                if ($chunk === '') {
                    usleep(10000);
                    continue;
                }

                $buffer .= $chunk;
                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines);

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!str_starts_with($line, 'data:')) continue;

                    $data = trim(substr($line, 5));
                    if ($data === '[DONE]') {
                        $onChunk("data: [DONE]\n\n");
                        return [
                            'usage' => [
                                'prompt_tokens' => $totalTokensIn,
                                'completion_tokens' => $totalTokensOut,
                                'total_tokens' => $totalTokensIn + $totalTokensOut,
                            ],
                            'content' => $fullContent,
                        ];
                    }

                    $onChunk("data: {$data}\n\n");

                    $parsed = json_decode($data, true);
                    if (isset($parsed['usage'])) {
                        // Groq usage format
                        $totalTokensIn = $parsed['usage']['prompt_tokens'] ?? $totalTokensIn;
                        $totalTokensOut = $parsed['usage']['completion_tokens'] ?? $totalTokensOut;
                    }
                    if (isset($parsed['x_groq']['usage'])) {
                        // Groq alternative format
                        $totalTokensIn = $parsed['x_groq']['usage']['prompt_tokens'] ?? $totalTokensIn;
                        $totalTokensOut = $parsed['x_groq']['usage']['completion_tokens'] ?? $totalTokensOut;
                    }
                    if (isset($parsed['choices'][0]['delta']['content'])) {
                        $fullContent .= $parsed['choices'][0]['delta']['content'];
                    }
                }
            }
        } catch (\Exception $e) {
            $onChunk("data: " . json_encode(['error' => ['message' => $e->getMessage()]]) . "\n\n");
            throw $e;
        }

        return [
            'usage' => [
                'prompt_tokens' => $totalTokensIn,
                'completion_tokens' => $totalTokensOut,
                'total_tokens' => $totalTokensIn + $totalTokensOut,
            ],
            'content' => $fullContent,
        ];
    }

    /**
     * Groq modellar ro'yxati
     */
    public function listModels(): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->get("{$this->baseUrl}/models");

        return $response->json('data', []);
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }
}
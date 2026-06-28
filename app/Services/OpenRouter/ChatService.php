<?php

namespace App\Services\OpenRouter;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $referer;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.key', env('OPENROUTER_API_KEY'));
        $this->baseUrl = config('services.openrouter.base_url', env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'));
        $this->referer = config('services.openrouter.referer', env('OPENROUTER_REFERER', 'https://cloudapi.uz'));
    }

    /**
     * Oddiy (non-streaming) so'rov
     */
    public function sendRequest(string $model, array $body, ?string $apiKey = null): array
    {
        $body['model'] = $model;
        $body['stream'] = false;

        $response = Http::withHeaders($this->headers($apiKey))
            ->timeout(120)
            ->post("{$this->baseUrl}/chat/completions", $body);

        if (!$response->successful()) {
            throw new \Exception($response->body() ?: 'OpenRouter request failed', $response->status());
        }

        return $response->json();
    }

    /**
     * Streaming so'rov — har chunk callback ga uzatiladi
     */
    public function streamRequest(string $model, array $body, callable $onChunk, ?string $apiKey = null): array
    {
        $body['model'] = $model;
        $body['stream'] = true;
        $body['stream_options'] = ['include_usage' => true]; // tokenlarni oxirida yuboradi

        $client = new \GuzzleHttp\Client(['timeout' => 300]);

        $totalTokensIn = 0;
        $totalTokensOut = 0;
        $fullContent = '';

        try {
            $response = $client->post("{$this->baseUrl}/chat/completions", [
                'headers' => $this->headers($apiKey),
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
                $buffer = array_pop($lines); // saqlash to'liq bo'lmagan qatorni

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

                    // Userga to'g'ridan-to'g'ri uzatish (proxy)
                    $onChunk("data: {$data}\n\n");

                    // Token statistikasini olish
                    $parsed = json_decode($data, true);
                    if (isset($parsed['usage'])) {
                        $totalTokensIn = $parsed['usage']['prompt_tokens'] ?? $totalTokensIn;
                        $totalTokensOut = $parsed['usage']['completion_tokens'] ?? $totalTokensOut;
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
     * Modellar ro'yxati (sync uchun)
     */
    public function listModels(): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->get("{$this->baseUrl}/models");

        return $response->json('data', []);
    }

    /**
     * Headerlar
     */
    protected function headers(?string $apiKey = null): array
    {
        return [
            'Authorization' => 'Bearer ' . ($apiKey ?? $this->apiKey),
            'HTTP-Referer' => $this->referer,
            'X-Title' => 'CloudAPI',
            'Content-Type' => 'application/json',
        ];
    }
}
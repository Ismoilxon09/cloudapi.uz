<?php

namespace App\Services\Agent\Mcp;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Minimal MCP (Model Context Protocol) klienti — Streamable HTTP transport.
 * JSON-RPC 2.0. Bitta endpoint: POST so'rovlar, javob JSON yoki SSE bo'lishi mumkin.
 *
 * Foydalanish:
 *   $c = new McpClient($url, $headers);
 *   $c->initialize();
 *   $tools = $c->listTools();
 *   $out = $c->callTool('name', ['a' => 1]);
 */
class McpClient
{
    protected const PROTOCOL_VERSION = '2024-11-05';

    protected int $id = 0;
    protected ?string $sessionId = null;
    protected bool $initialized = false;

    public function __construct(
        protected string $url,
        protected array $headers = [],
        protected int $timeout = 30,
    ) {}

    /** Handshake — server bilan sessiya ochish. */
    public function initialize(): array
    {
        $result = $this->request('initialize', [
            'protocolVersion' => self::PROTOCOL_VERSION,
            'capabilities'    => (object) [],
            'clientInfo'      => ['name' => 'CloudAPI Agents', 'version' => '1.0.0'],
        ]);

        // initialized notification (javob kutilmaydi)
        $this->notify('notifications/initialized');
        $this->initialized = true;

        return $result;
    }

    /** Mavjud toollar ro'yxati (kursorlar bo'ylab). */
    public function listTools(): array
    {
        if (!$this->initialized) $this->initialize();

        $tools = [];
        $cursor = null;
        $guard = 0;
        do {
            $params = $cursor ? ['cursor' => $cursor] : [];
            $res = $this->request('tools/list', $params);
            foreach (($res['tools'] ?? []) as $t) {
                $tools[] = $t;
            }
            $cursor = $res['nextCursor'] ?? null;
        } while ($cursor && ++$guard < 20);

        return $tools;
    }

    /** Toolni chaqirish → matn natija. */
    public function callTool(string $name, array $arguments = []): string
    {
        if (!$this->initialized) $this->initialize();

        $res = $this->request('tools/call', [
            'name'      => $name,
            'arguments' => (object) $arguments,
        ]);

        // result.content — bloklar massivi ({type:text,text}, ...)
        $parts = [];
        foreach (($res['content'] ?? []) as $block) {
            if (($block['type'] ?? '') === 'text') {
                $parts[] = $block['text'] ?? '';
            } elseif (isset($block['text'])) {
                $parts[] = $block['text'];
            } else {
                $parts[] = json_encode($block, JSON_UNESCAPED_UNICODE);
            }
        }
        $text = trim(implode("\n", $parts));

        if (($res['isError'] ?? false)) {
            return 'ERROR: ' . ($text ?: 'tool xatosi');
        }
        return $text !== '' ? $text : '(bo\'sh natija)';
    }

    // === Ichki ===

    protected function request(string $method, array $params): array
    {
        $payload = [
            'jsonrpc' => '2.0',
            'id'      => ++$this->id,
            'method'  => $method,
            'params'  => (object) $params,
        ];

        $response = $this->send($payload);
        $data = $this->parseResponse($response);

        if (isset($data['error'])) {
            $msg = $data['error']['message'] ?? 'MCP xatosi';
            throw new McpException("MCP {$method}: {$msg}");
        }
        return $data['result'] ?? [];
    }

    protected function notify(string $method, array $params = []): void
    {
        try {
            $this->send([
                'jsonrpc' => '2.0',
                'method'  => $method,
                'params'  => (object) $params,
            ]);
        } catch (\Throwable $e) {
            // notification — javob shart emas
        }
    }

    protected function send(array $payload): Response
    {
        $headers = array_merge($this->headers, [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json, text/event-stream',
            'MCP-Protocol-Version' => self::PROTOCOL_VERSION,
        ]);
        if ($this->sessionId) {
            $headers['Mcp-Session-Id'] = $this->sessionId;
        }

        $response = Http::withHeaders($headers)
            ->timeout($this->timeout)
            ->withBody(json_encode($payload), 'application/json')
            ->post($this->url);

        // Sessiya id ni eslab qolish
        if ($sid = $response->header('Mcp-Session-Id')) {
            $this->sessionId = $sid;
        }

        if ($response->status() >= 400) {
            throw new McpException('MCP HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 200));
        }

        return $response;
    }

    /** JSON yoki SSE javobdan JSON-RPC xabarini ajratib olish. */
    protected function parseResponse(Response $response): array
    {
        $contentType = strtolower($response->header('Content-Type') ?? '');
        $body = $response->body();

        // Notification/bo'sh (202 Accepted) — javob yo'q
        if (trim($body) === '') {
            return ['result' => []];
        }

        if (str_contains($contentType, 'text/event-stream')) {
            return $this->parseSse($body);
        }

        $json = json_decode($body, true);
        if (!is_array($json)) {
            throw new McpException('MCP javobini o\'qib bo\'lmadi');
        }
        return $json;
    }

    /** SSE oqimidan oxirgi (natijali) JSON-RPC xabarini olish. */
    protected function parseSse(string $body): array
    {
        $result = null;
        foreach (preg_split('/\r?\n/', $body) as $line) {
            $line = trim($line);
            if (!str_starts_with($line, 'data:')) continue;
            $data = trim(substr($line, 5));
            if ($data === '' || $data === '[DONE]') continue;
            $json = json_decode($data, true);
            if (is_array($json) && (isset($json['result']) || isset($json['error']))) {
                $result = $json; // oxirgi natijali xabarni saqlaymiz
            }
        }
        if ($result === null) {
            throw new McpException('MCP SSE javobida natija topilmadi');
        }
        return $result;
    }
}

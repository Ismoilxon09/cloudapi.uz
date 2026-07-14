<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Agentga ulangan MCP (Model Context Protocol) serverlari — tool manbalari
        Schema::create('agent_mcp_servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url');                              // MCP endpoint (Streamable HTTP)
            $table->enum('transport', ['http', 'sse'])->default('http');
            $table->text('headers')->nullable();                // shifrlangan JSON (auth va h.k.)
            $table->boolean('enabled')->default(true);
            $table->enum('status', ['unknown', 'ok', 'error'])->default('unknown');
            $table->json('tools')->nullable();                  // topilgan toollar keshi
            $table->unsignedInteger('tools_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_mcp_servers');
    }
};

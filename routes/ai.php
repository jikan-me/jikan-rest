<?php

use App\Http\Middleware\AuthenticateMcp;
use App\Mcp\Servers\JikanServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP servers
|--------------------------------------------------------------------------
| Read-only MCP endpoint over HTTP. The bearer-token guard wraps the whole
| group so every method registered by Mcp::web() (POST plus the 405 stubs for
| GET/DELETE) is protected and fails closed when MCP_API_KEY is unset.
| See docs/MCP.md.
*/

Route::middleware(AuthenticateMcp::class)->group(function (): void {
    Mcp::web('/mcp', JikanServer::class);
});

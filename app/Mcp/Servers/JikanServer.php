<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetAnimeTool;
use App\Mcp\Tools\SearchAnimeTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('Jikan REST')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
Read-only access to the Jikan v4 anime catalog. Use search_anime to find
anime by title and get_anime to fetch one entry by MyAnimeList id. Results
mirror the public v4 API and are bounded in size. This server cannot write
any data.
MARKDOWN)]
class JikanServer extends Server
{
    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        SearchAnimeTool::class,
        GetAnimeTool::class,
    ];
}

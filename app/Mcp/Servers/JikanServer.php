<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CompareAnimeTool;
use App\Mcp\Tools\GetAnimeRelationsTool;
use App\Mcp\Tools\GetAnimeTool;
use App\Mcp\Tools\GetSeasonalAnimeTool;
use App\Mcp\Tools\SearchAnimeTool;
use App\Mcp\Tools\SearchCharactersTool;
use App\Mcp\Tools\SearchMangaTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('Jikan REST')]
#[Version('1.1.0')]
#[Instructions(<<<'MARKDOWN'
Read-only access to the Jikan v4 catalog. Use search_anime, search_manga and
search_characters to search by title/name, get_anime and get_anime_relations
to fetch one anime entry or its related entries, get_seasonal_anime for a
year/season listing, and compare_anime to fetch 2-5 anime entries in one call.
Results mirror the public v4 API and are bounded in size. This server cannot
write any data.
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
        SearchMangaTool::class,
        SearchCharactersTool::class,
        GetAnimeRelationsTool::class,
        GetSeasonalAnimeTool::class,
        CompareAnimeTool::class,
    ];
}

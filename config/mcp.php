<?php

/*
|--------------------------------------------------------------------------
| MCP server (read-only, opt-in)
|--------------------------------------------------------------------------
|
| Laravel MCP merges its own defaults for this file (see mergeConfigFrom in
| Laravel\Mcp\Server\McpServiceProvider), so only the deployment-specific keys
| live here. `api_key` has no package default and must be supplied by the
| operator: empty means /mcp is disabled and fails closed with 401.
|
*/
return [
    'api_key' => env('MCP_API_KEY'),
];

# Artisan Commands

Artisan commands are run in the root of the project directory where the `artisan` file is present.
For an entire list of commands, you can run `php artisan list`

## Index
- [Serve](#serve)
- [Blacklist](#blacklist)
    - [Add](#blacklist-add)
    - [Remove](#blacklist-remove)
    - [Flush](#blacklist-flush)
- [Queue](#queue)
    - [Clear](#queue-clear)
- [Cache](#cache)
    - [Remove](#cache-remove)
    - [Change Cache Driver](#cache-change-cache-driver)
    - [Change Cache Method](#cache-change-cache-method)

## Commands

### Serve
Command: `serve`
Example: `php artisan serve`

Serve the application on the PHP development server 

### Blacklist
Add IPs to your API's blacklist. A blacklisted IP will get a HTTP 403 with the following JSON body:
```json
{
    "status": 403,
    "type": null,
    "message": "You have been blocked from the service for breaching Terms of Use",
    "error": null
}
```

The blacklist is stored in `/storage/app/blacklist.json`. Once your application starts, the blacklist is loaded into memory via Redis.

#### Blacklist: Add
Command: `blacklist:add {ip} {--reload}`

Example: `php artisan blacklist:add 123.456.789.10`

**Note:** Without `--reload`, the blacklist won't take immediate effect as it will only be saved to `blacklist.json` and won't updated in-memory. 

Therefore, it's recommended to use `--reload` for real-time effect.

#### Blacklist: Remove
Command: `blacklist:remove {ip} {--reload}`

Example: `php artisan blacklist:remove 123.456.789.10`

**Note:** Without `--reload`, the blacklist won't take immediate effect as it will only be removed from `blacklist.json` and won't be updated in-memory. 

Therefore, it's recommended to use `--reload` for real-time effect.

#### Blacklist: Flush
Command: `blacklist:flush {--reload}`

Example: `php artisan blacklist:flush`

**Note:** Without `--reload`, the blacklist won't take immediate effect as it will only empty `blacklist.json` and won't be updated in-memory. 

Therefore, it's recommended to use `--reload` for real-time effect.

### Queue

#### Queue: Clear
Command: `queue:clear`

Example: `php artisan queue:clear`

This will empty any cache updater jobs that are currently in queue if you're using the [Queue Cache Method](https://github.com/jikan-me/jikan-rest/blob/master/README.md#06-configuring-how-jikan-handles-expired-cache-optional)

### Cache

#### Cache: Remove
Command: `cache:remove {key}`

Example: `php artisan cache:remove request:anime:d6092f2422f084452c84555f17c7ba047e6998d3`

This will remove the cache of a specific request. You can retrieve any request's hash key by visiting the request.

In v2-v3, the request hash key is defined in the JSON response body as `request_hash`.

In v3.4+, the request is defined in the response headers as `X-Request-Hash` as well.

In v4+, the request is defined only in the response header.

#### Cache: Change Cache Driver
Command: `cache:driver {driver}`

Example: `cache:driver redis`

[Read more on how it works](https://github.com/jikan-me/jikan-rest/blob/master/README.md#05-configuring-how-jikan-caches-optional)

#### Cache: Change Cache Method
Command: `cache:method {method}`

Example: `cache:method queue`

[Read more on how it works](https://github.com/jikan-me/jikan-rest/blob/master/README.md#06-configuring-how-jikan-handles-expired-cache-optional)
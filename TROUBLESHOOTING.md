# Troubleshooting Guide

## My local instance keeps returning HTTP 500

First off, check `storage/logs/lumen.log`.

**1. If you're getting a lot of Redis errors along the lines of `BGSAVE` failing.**
You probably have a low RAM resource, switch over to file caching `CACHE_DRIVER=file`.

Redis throws this error even if you have about 1/4 of your RAM free. This is because it does a background save of your entire Redis cache - which is stored in-memory and which fails without the sufficient required memory.

You can stop Redis from haggling you and override that by running the following command: `redis-cli config set stop-writes-on-bgsave-error no`

**2. Jikan is failing to cache (when `CACHE_DRIVER=file`) because the "disk is out of space" - but you're sure you have enough space available.**

Sorry! This is due to a bug on a previous release ([Issue #59](https://github.com/jikan-me/jikan-rest/issues/59)), please make sure you're upto date.

**Recovery Procedure**
- `sudo service supervisor stop`
- Delete Lumen & worker logs: `rm storage/logs/lumen.log` + `rm storage/logs/worker.log`
- Run `lsof | grep deleted` to check for the "Lumen.log" process, you'll know when it has a bizzare amount of space allocated to the process to it. Copy the process ID and then kill it; `kill [process id]` e.g `kill 12345`
- Make sure your Jikan instance is on the latest release. Run a `git pull` and then `composer update`
- Reduce the number of supervisor processes in `/etc/supervisor/conf.d/jikan-worker.conf` e.g `numprocs=1`

And then reload the supervisor configuration:

`sudo service supervisor start`

`sudo supervisorctl reread`

`sudo supervisorctl reload`

`sudo supervisorctl update`

`sudo supervisorctl start jikan-worker:*`

- Restart everything:
`sudo service supervisor restart`

`sudo service apache2 restart`

`sudo service redis restart`

If Redis is taking too long to restart, follow this: https://stackoverflow.com/a/45069100/2326811 and then start it `sudo service redis start`

## My local instance is returning HTTP 503

 This is an error forwarded from MAL, it typically happens when MAL is down. The Jikan response body includes the HTTP status from MAL, like: `[HTTP code] on [request url]` 

## I want to clear the cache in Jikan
`php artisan cache:clear`

## I want to clear the Cache Updater Queue
1. `sudo service supervisor stop`

2. `redis-cli --scan --pattern queue_update:* | xargs redis-cli del` or alternatively replace `del` with `unlink` to have it done in the background ([Redis 4.0.0 required](https://redis.io/commands/unlink))

3. `redis-cli --scan --pattern queues:* | xargs redis-cli del` or alternatively replace `del` with `unlink` to have it done in the background ([Redis 4.0.0 required](https://redis.io/commands/unlink))

4. `php artisan queue:restart`

5. `sudo service supervisor start`


More troubleshooting Q/A on the way, please let me know if there's anything else I should add onto here.

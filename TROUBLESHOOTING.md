# Troubleshooting Guide

## My local instance keeps returning HTTP 500

1. Check `storage/logs/lumen.log`.

2a. If you're getting a Redis error along the lines of `BGSAVE` failing.
You're probably using `redis` as your `CACHE_DRIVER` (in `.env`). You probably have low RAM resource, switch over to file caching `CACHE_DRIVER=file`.

Redis throws this error even if you have about 1/4 of your RAM free. This is because it does a background save of your entire Redis cache - which is stored in-memory and which fails without the sufficient required memory.
You can stop Redis from haggling you and override that by running the following command: `redis-cli config set stop-writes-on-bgsave-error`

2b. Jikan is failing to cache (when `CACHE_DRIVER=file`) because the server is out of space - but you're sure you have enough space
Sorry! This is due to a bug on a previous release ([Issue #59](https://github.com/jikan-me/jikan-rest/issues/59)), please make sure you're upto date with on the releases

**Recovery Procedure**
- `sudo service supervisor stop`
- Delete Lumen logs: `rm storage/logs/lumen.log`
- Run `lsof | grep deleted` to check for the Lumen.log process, you'll know when it has a bizzare amount of space allocated to the process to it. 
- Make sure your Jikan instance is on the latest release. Run a `git pull` and then `composer update`
- Reduce the number of supervisor processes in `/etc/supervisor/conf.d/jikan-worker.conf` e.g `numprocs=1`
- Restart everything:
`sudo service supervisor restart`
`sudo service apache2 restart`
`sudo service redis restart`

If Redis is taking too long to restart, follow this: https://stackoverflow.com/a/45069100/2326811 and then start it `sudo service redis start`




More troubleshooting Q/A on the way, please let me know if there's anything else I should add onto here.

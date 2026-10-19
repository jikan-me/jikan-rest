# Troubleshooting Guide

## My local instance keeps returning HTTP 500

First off, check the log: `storage/logs/daily/jikan-YYYY-MM-DD.log` (default `LOG_CHANNEL=stack` → daily), or `docker compose logs app` in Docker.

**1. If you're getting a lot of Redis errors along the lines of `BGSAVE` failing.**
You probably have a low RAM resource, switch over to file caching `CACHE_DRIVER=file`.

Redis throws this error even if you have about 1/4 of your RAM free. This is because it does a background save of your entire Redis cache - which is stored in-memory and which fails without the sufficient required memory.

You can stop Redis from haggling you and override that by running the following command: `redis-cli config set stop-writes-on-bgsave-error no`

**2. Jikan is failing to cache (when `CACHE_DRIVER=file`) because the "disk is out of space" - but you're sure you have enough space available.**

> Legacy (pre-4.3, Lumen + supervisor/apache deployments). 4.3.0 runs on Octane; see `docs/DOCKER.md` / `docs/SETUP.md`.

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

> Legacy (pre-4.3): the cache-updater jobs were removed in 4.3.0, so there is no updater queue to clear. Kept for older deployments.

1. `sudo service supervisor stop`

2. `redis-cli --scan --pattern queue_update:* | xargs redis-cli del` or alternatively replace `del` with `unlink` to have it done in the background ([Redis 4.0.0 required](https://redis.io/commands/unlink))

3. `redis-cli --scan --pattern queues:* | xargs redis-cli del` or alternatively replace `del` with `unlink` to have it done in the background ([Redis 4.0.0 required](https://redis.io/commands/unlink))

4. `php artisan queue:restart`

5. `sudo service supervisor start`


## Typesense segfaults on start under WSL2 (kernel without `/proc/self/io`)

Symptom: a native `typesense-server` binary (tested with 27.1) crashes with a
segmentation fault right after start-up on WSL2. Cause: some WSL2 kernels
(seen on 6.18.x) do not expose `/proc/self/io`, which Typesense reads for its
process metrics, and it dereferences the missing file.

Workaround (development environment only): preload a tiny shim that redirects
opens of `/proc/self/io` to a file containing zeroed counters.

```sh
mkdir -p /tmp/shim && cd /tmp/shim
printf 'rchar: 0
wchar: 0
syscr: 0
syscw: 0
read_bytes: 0
write_bytes: 0
cancelled_write_bytes: 0
' > fakeio.txt
cat > shim.c <<'EOF'
#define _GNU_SOURCE
#include <dlfcn.h>
#include <fcntl.h>
#include <stdarg.h>
#include <stdio.h>
#include <string.h>
#define FAKE "/tmp/shim/fakeio.txt"
#define FIX(p) ((p) && !strcmp((p), "/proc/self/io") ? FAKE : (p))
#define OPEN(name) int name(const char *p, int f, ...) { static int (*r)(const char *, int, ...); if (!r) r = dlsym(RTLD_NEXT, #name); va_list a; va_start(a, f); int m = va_arg(a, int); va_end(a); return r(FIX(p), f, m); }
#define OPENAT(name) int name(int d, const char *p, int f, ...) { static int (*r)(int, const char *, int, ...); if (!r) r = dlsym(RTLD_NEXT, #name); va_list a; va_start(a, f); int m = va_arg(a, int); va_end(a); return r(d, FIX(p), f, m); }
#define FOPEN(name) FILE *name(const char *p, const char *m) { static FILE *(*r)(const char *, const char *); if (!r) r = dlsym(RTLD_NEXT, #name); return r(FIX(p), m); }
OPEN(open) OPEN(open64) OPENAT(openat) OPENAT(openat64) FOPEN(fopen) FOPEN(fopen64)
EOF
gcc -shared -fPIC -o shim.so shim.c -ldl
LD_PRELOAD=/tmp/shim/shim.so ./typesense-server --data-dir=./data --api-key=testkey --api-port=8109
```

The shim intercepts `open`, `open64`, `openat`, `openat64`, `fopen` and
`fopen64` for exactly the path `/proc/self/io` and opens the zeroed
`fakeio.txt` (`rchar`, `wchar`, `syscr`, `syscw`, `read_bytes`, `write_bytes`,
`cancelled_write_bytes`) instead; every other path passes through untouched.

This is a local dev-environment workaround, not a Jikan or Typesense bug fix.
It is not needed with the official Docker image on a normal kernel, nor in CI.

More troubleshooting Q/A on the way, please let me know if there's anything else I should add onto here.

## MongoDB exits at startup: "Linux kernel versions 6.19 and newer"

MongoDB 8.0 refuses to start on Linux kernel 6.19 or newer and logs
`MongoDB cannot start: Linux kernel versions 6.19 and newer has a known incompatibility`
([SERVER-121912](https://jira.mongodb.org/browse/SERVER-121912)). This affects the
`mongo` container on hosts with a new kernel, including GitHub's Ubuntu 26 runners
(CI pins `ubuntu-24.04` for that reason). Run MongoDB on a host with an older kernel,
or move to a MongoDB release that has the fix once one is available.


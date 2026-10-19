# 🐳 Running Jikan API in a container

> **4.3.0:** the maintained Docker instructions are in [`DOCKER.md`](DOCKER.md)
> (`docker compose up -d --build`, then `docker compose exec app php artisan migrate`).
> This page describes the older `container-setup.sh` / published-image flow; parts of it
> (secret files, the `jikanme/jikan-rest` Docker Hub image) predate 4.3.0. Upgrading a 4.2.x
> instance: see [`UPGRADING.md`](UPGRADING.md).

The most easiest way to get started is to use our container setup cli script after checking out the repo with git (linux only):

```bash
./container-setup.sh start
```

This will:

- Prompt you for the required passwords and usernames
- Starts the `docker-compose.yml` stack (`app`, `mongodb`, `redis`, and `typesense` via `docker-compose.typesense.yml`)
- CORS headers are off by default (`CORS_MIDDLEWARE=false`); set `CORS_MIDDLEWARE=true` if your proxy does not add them.

> **Note**: The script supports both `docker` and `podman`. In case of `podman` please bare in mind that sometimes the container name resolution doesn't work on the container network. 
> In those cases you might have to install `aardvark-dns` package. On `Arch Linux` podman uses `netavark` network by default (in 2023) so you will need to install the before mentioned package.

> **Note 2**: The script will start the jikan API, but if you start it for the first  time, it won't have any data in it!
> You will have to run the indexers through artisan to have data. See ["Running the indexer with the script"](#running-the-indexer-with-the-script) section.

The script has the following prerequisites and will notify you if these are not present:

- git
- `docker` or `podman`
- `docker-compose` or `podman-compose`

### Available commands in the cli script

```
============================================================
Jikan API Container Setup CLI
============================================================
Syntax: ./container-setup.sh [command]
---commands---
help                   Print CLI help
build-image            Build Image Locally
start                  Start Jikan API (mongodb, typesense, redis, jikan-api workers)
stop                   Stop Jikan API
validate-prereqs       Validate pre-reqs installed (docker, docker-compose)
execute-indexers       Execute the indexers, which will scrape and index data from MAL. (Notice: This can take days)
index-incrementally    Executes the incremental indexers for each media type. (anime, manga)
```

### Running the indexer with the script

When you first startup the app you will have an empty database. To fill it up you can execute the following command:

```bash
./container-setup.sh execute-indexers
```

Please note that this command can take 4-5 days to run. You can run it in the background with the `&` marker:

```bash
./container-setup.sh execute-indexers &
```

If interrupted then you will have to manually resume the indexing, otherwise the above command will just start again from the beginning.

### Updating to a newer version

You need to stop the app first:

```bash
./container-setup.sh stop
```

Then remove the jikan-api image from your local storage and pull the new one. Set the `JIKAN_API_VERSION` environment variable to the latest image tag. This can be either `latest` or a version tag such as `v4.3.0`.

```bash
JIKAN_API_VERSION=latest ./container-setup.sh start
```

## More customised setups

Some of you might only want to run the `jikan-rest` app with only mongodb, without the more sophisticated search functionality. In those cases we don't have a `docker-compose` config for you. You need to start the `jikan-rest` container with atleast a `mongodb` instance.
The `jikan-rest` container will require a `.env` file mounted where you configure the credentials for `mongodb`.


```bash
docker run -d --name=jikan-rest -p 8080:8080 -v ./.env:/app/.env jikanme/jikan-rest:latest
```

> **Image versions:** from 4.3.0 on, images are published to Docker Hub by tagging a release
> (`vX.Y.Z` → `jikanme/jikan-rest:X.Y.Z`, `:X.Y` and `:latest`). Until `v4.3.0` has been
> published there, `jikanme/jikan-rest:latest` is still the old 4.2.x image — build 4.3.0 from
> source instead (`docker compose up -d --build`, see [`DOCKER.md`](DOCKER.md), or the
> `docker build` example below) and pin a version tag (e.g. `jikanme/jikan-rest:4.3`) once it exists.

- Container listens on port `8080` for http requests
- By mounting your .env file on the container via `-v ./.env:/app/.env` command line option for `docker run` you can
  configure Jikan API.

> **Important**: You need to either mount a `.env` file on the container or specify the configuration through
> environment variables to make Jikan API work in the container. Jikan API needs a MongoDB and optionally a search engine.
> In high load environments additionally a `redis` server is required too. The configuration should point to the correct
> address of these services.

> **Tip**: If you run the container on a non-default network, you can use the container names in the configuration to
> specify the address of services like MongoDB and TypeSense.

There is also a `Dockerfile` in the repo which you can use to build the container image and startup the app in a
container:

```bash
docker build -t jikan-rest:nightly .
docker run -d --name=jikan-rest -p 8080:8080 -v ./.env:/app/.env jikan-rest:nightly
```

> Once a 4.3.x image is published, it's usually enough to use the image from [Docker Hub](https://hub.docker.com/r/jikanme/jikan-rest).

### Docker compose usage

```bash
docker compose up -d --build
```

This builds the app from the local `Dockerfile` and starts MongoDB, Redis and
Typesense alongside it. Unlike the `container-setup.sh` flow it needs **no secret
files**: the local MongoDB/Redis run without authentication by default (drop the
`ports:` stanzas if you already run those services on the host).

Full build / run / env (`APP_KEY`, `TYPESENSE_API_KEY`, `SCOUT_DRIVER`,
`CACHE_DRIVER`) / seeding / health instructions are in
[`DOCKER.md`](DOCKER.md). On Apple Silicon no build argument is needed: the Dockerfile
detects the architecture and fetches the matching RoadRunner binary.

> The old secret-file flow (db_username.txt / redis_password.txt /
> typesense_api_key.txt + `docker/config/.env.compose`) is kept for the
> `container-setup.sh` script and the published image; it is no longer used by
> the root `docker-compose.yml`.

### Note for Podman

If you build the container image yourself with podman, the resulting image format will be OCI by default.
To make the health checks work in that situation you need to run the container the following way:

```bash
podman run -d --name=jikan-rest -p 8080:8080 -v ./.env:/app/.env --health-start-period=5s --health-cmd="wget -q -O /dev/null http://127.0.0.1:8080/up || exit 1" jikan-rest:nightly
```

### Configuration of the container

You can also change the settings of Jikan through setting environment variables via the `-e` command line argument option for
the `docker run` command.
These environment variables are the same as the options found in the `.env` file. We also provide a sample file
called `.env.dist`.      
Additionally, you can use the `--env-file` option of `docker run` to specify configuration for Jikan, in which case you
put all the configuration in the env file.

```bash
docker run -d --name=jikan-rest -p 8080:8080 --env-file ./env.list jikanme/jikan-rest:latest
```

The env-file should contain env var value pairs line by line.

```
VAR1=value1
VAR2=value2
```

There are additional configuration options:

| Name                                 | Description                                                                                                                                                                                         |
|--------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| OCTANE_WORKERS                       | (Number or `auto`) Octane/RoadRunner worker processes. Default `auto` (one per CPU core). Replaces `RR_MAX_WORKER_MEMORY` tuning.                                                                    |
| OCTANE_MAX_REQUESTS                  | (Number) Requests a worker serves before it is recycled (bounds memory growth). Default `500`.                                                                                                      |
| QUEUE_WORKERS                        | (Number) `php artisan queue:work` processes started by the entrypoint. Default `0`. Replaces `JIKAN_QUEUE_WORKER_PROCESS_NUM`.                                                                     |
| JIKAN_ENABLE_PERIODICAL_FULL_INDEXER | (Bool) Configures whether to run the anime/manga indexer every week, which would crawl all anime/manga at first then it would just grab the latest anime/manga entries from MAL. Defaults to false. |

You can read more about additional configuration options on
the [Configuration Wiki page](https://github.com/jikan-me/jikan-rest/wiki/Configuration).

## Some facts about the container image

- Jikan uses Laravel Octane on RoadRunner as the application server within the container (port 8080, healthcheck `GET /up`).
- Both `wget` and `curl` exist in the container image.
- The entrypoint (`docker-entrypoint.sh` + `docker-entrypoint.php`) sets safe defaults:
  - file cache (`CACHE_DRIVER=file`), no Redis caching
  - no search engine (`SCOUT_DRIVER=null`, Mongo `$text` fallback)
- Processes running in the container:
  - the Octane/RoadRunner workers serving HTTP requests
  - [Supercronic](https://github.com/aptible/supercronic), which runs the Laravel scheduler
  - `QUEUE_WORKERS` queue workers (none by default), for queued search imports and other background jobs.

# 🐳 Running Jikan API in a container

```bash
docker run -d --name=jikan-rest -p 8080:8080 -v ./.env:/app/.env jikanme/jikan-rest:latest
```

- Container listens on port `8080` for http requests
- By mounting your .env file on the container via `-v ./.env:/app/.env` command line option for `docker run` you can
  configure Jikan API.

> **Important**: You need to either mount a `.env` file on the container or specify the configuration through
> environment variables to make Jikan API work in the container. Jikan API needs a MongoDB and optionally a search engine.
> In high load environments additionally a `redis` server is required too. The configuration should point to the correct
> address of these services.

> **Tip**: If you run the container on a non-default network, you can use the container names in the configuration to
> specify the address of services like MongoDB and TypeSense. However, this is not a concern if you
> use `docker-compose`.

There is also a `Dockerfile` in the repo which you can use to build the container image and startup the app in a
container:

```bash
docker build -t jikan-rest:nightly .
docker run -d --name=jikan-rest -p 8080:8080 -v ./.env:/app/.env jikan-rest:nightly
```

If you need a different CPU architecture, set the `TARGET_ARCH` build argument:

```bash
docker build -t jikan-rest:nightly --build-arg TARGET_ARCH=arm64 .
```

`TARGET_ARCH` is `amd64` by default.

### Docker compose usage

```
docker-compose up
```

Docker compose will use the `.env` file from the folder where you execute it from to load configurations for the
services. If you don't have a `.env` file yet in the folder, copy the `.env.dist` file, and set the passwords.

> **Please note**: The syntax rules of docker compose for `.env` applies
> here: https://docs.docker.com/compose/env-file/#syntax-rules

#### Note for Podman

If you build the container image yourself with podman, the resulting image format will be OCI by default.
To make the health checks work in that situation you need to run the container the following way:

```bash
podman run -d --name=jikan-rest -p 8080:8080 -v ./.env:/app/.env --health-start-period=5s --health-cmd="curl --fail http://localhost:2114/health?plugin=http || exit 1" jikan-rest:nightly
```

#### Configuration of the container

You can change the settings of Jikan through setting environment variables via the `-e` command line argument option for
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
| RR_MAX_WORKER_MEMORY                 | (Number) Configures the available memory in megabytes for the php scripts                                                                                                                           |
| RR_MAX_REQUEST_SIZE_MB               | (Number) Configures the max allowed request body size in megabytes                                                                                                                                  |
| JIKAN_QUEUE_WORKER_PROCESS_NUM       | (Number) Configures the number of running queue worker processes. (You want to increase this if you experience huge load)                                                                           |
| JIKAN_ENABLE_PERIODICAL_FULL_INDEXER | (Bool) Configures whether to run the anime/manga indexer every week, which would crawl all anime/manga at first then it would just grab the latest anime/manga entries from MAL. Defaults to false. |

You can read more about additional configuration options on
the [Configuration Wiki page](https://github.com/jikan-me/jikan-rest/wiki/Configuration).

## Some facts about the container image

- Jikan uses RoadRunner as an application server within the container.
- Both `wget` and `curl` exists in the container image.
- The script in `docker-entrypoint.php` sets safe defaults. Because of this by default the app won't behave the same way
  as the publicly available version of the app at [https://api.jikan.moe/v4](https://api.jikan.moe/v4). The default
  settings:
  - No redis caching
  - No search index usage (inaccurate search results)
- Via Roadrunner multiple processes are running in the container, and their logs are aggregated and forwarded
  to `stdout`.
  - These processes are:
    - the php processes ingesting the http requests
    - [Supercronic](https://github.com/aptible/supercronic), which runs cron jobs.
    - Queue workers for populating the search index and other background jobs.
- The container does all the scheduled background jobs automatically out-of-the-box:
  - Importing new documents into the search index.
  - Updating entries from upstream.

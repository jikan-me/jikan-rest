# 🐳 Running Jikan API in a container

The most easiest way to get started is to use our container setup cli script after checking out the repo with git (linux only):

```bash
./container-setup.sh start
```

This will:

- Prompt you for the required passwords and usernames
- Sets up a production ready setup with `redis`, `typesense` and `mongodb` (almost same as the public api at `api.jikan.moe`)
- Sets mongodb to use max 1gb of memory
- Configures jikan-api to add CORS headers to responses.

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
index-incrementally    Executes the incremental indexers for each media type. (anime, manga, character, people)
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

Then remove the jikan-api image from your local storage and pull the new one. Set the `JIKAN_API_VERSION` environment variable to the latest image tag. This can be either `latest` or the version `v4.0.0-11`.

```bash
JIKAN_API_VERSION=latest ./container-setup.sh start
```

## More customised setups

Some of you might only want to run the `jikan-rest` app with only mongodb, without the more sophisticated search functionality. In those cases we don't have a `docker-compose` config for you. You need to start the `jikan-rest` container with atleast a `mongodb` instance.
The `jikan-rest` container will require a `.env` file mounted where you configure the credentials for `mongodb`.


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
> specify the address of services like MongoDB and TypeSense.

There is also a `Dockerfile` in the repo which you can use to build the container image and startup the app in a
container:

```bash
docker build -t jikan-rest:nightly .
docker run -d --name=jikan-rest -p 8080:8080 -v ./.env:/app/.env jikan-rest:nightly
```

> Most of the time it's enough to just use the image from [Docker Hub](https://hub.docker.com/r/jikanme/jikan-rest).

### Docker compose usage

```
docker-compose up
```

This does the same thing as the `container-setup.sh` script mostly, but you will have to create the secret files yourself. The following secret files are required for credentials (put them next to the `docker-compose.yml` file):

- db_admin_password.txt
- db_admin_username.txt
- db_password.txt
- db_username.txt
- redis_password.txt
- typesense_api_key.txt

You can customise the Jikan API config through `./docker/config/.env.compose` file. (E.g. you don't want CORS headers)

> **Please note**: The syntax rules of docker compose for `.env` applies
> here: https://docs.docker.com/compose/env-file/#syntax-rules

> **Additional configuration**: You can change the mongodb memory usage via `MONGO_CACHE_SIZE_GB` environment variable. 
> It sets how many gigabytes of memory is available for wired tiger. Default is `1`. This is useful for systems with low memory capacity.

### Note for Podman

If you build the container image yourself with podman, the resulting image format will be OCI by default.
To make the health checks work in that situation you need to run the container the following way:

```bash
podman run -d --name=jikan-rest -p 8080:8080 -v ./.env:/app/.env --health-start-period=5s --health-cmd="curl --fail http://localhost:2114/health?plugin=http || exit 1" jikan-rest:nightly
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

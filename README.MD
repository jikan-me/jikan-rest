![Jikan](http://i.imgur.com/ctoJ3Jp.png)
# Jikan - Unofficial MyAnimeList.net REST API
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Percentage of issues still open") [![stable](https://img.shields.io/badge/PHP-^%207.1-blue.svg?style=flat)]() [![Discord Server](https://img.shields.io/discord/460491088004907029.svg?style=flat&logo=discord)](https://discordapp.com/invite/4tvCr36)

Jikan is a REST API for [MyAnimeList.net](https://myanimelist.net). It scrapes the website to satisfy the need for an API - which MyAnimeList lacks.

The raison d'être of Jikan is to assist developers easily get the data they need for their apps and projects without having to depend on the lackluster official API, unstable APIs, or sidetracking their projects to develop parsers.

The word _Jikan_ literally translates to _Time_ in Japanese (**時間**). And that's what this API saves you of. ;)


**Notice**: Jikan does not support authenticated requests. You can not update your lists.

## Index
- [Getting Started](#getting-started)
    - [Requirements](#requirements)
    - [🐳 Docker](#-docker)
    - [Installation Prerequisites](#01-installation-prerequisites)
    - [Installation](#02-installation)
    - [Configuration](#03-configuration)
    - [Ignition](#04-ignition)
    - [Configuring Cache Driver](#05-configuring-how-jikan-caches-optional) (optional)
    - [Configuring Cache Method](#06-configuring-how-jikan-handles-expired-cache-optional) (optional)
    - [Configuring Supervisord](#configuring-supervisord) (optional)
- [Troubleshooting](#troubleshooting)
- [Artisan Commands](#artisan-commands)
- [Information](#information)
- [Wrappers](#wrappers)
- [Running Tests](#running-tests)
- [Backers](#backers)
- [Disclaimer](#disclaimer)

## Getting Started

### Requirements

- PHP 7.1+
- [Composer](https://getcomposer.org/download/)
- [Redis](https://redis.io)
- [Supervisord](http://supervisord.org/) (optional)

#### 🐳 Docker
If you don't want to install it yourself, there are dockerized options available:

- [JikanREST Docker](https://github.com/fethica/jikan-rest-docker) by Fethi El Hassasna
- [JikanDocerized](https://github.com/zunjae/JikanDockerized) by Zunjae

### 01. Installation Prerequisites

#### 01A. Linux

This is specifically for Ubuntu, but other distributions should work similarly.
1. Install requirements:
  - Add PHP related packages: `sudo add-apt-repository -y ppa:ondrej/php`
    - If `add-apt-repository` is not installed, you can install it by doing `sudo apt install python-software-properties` or `sudo apt install software-properties-common`
  - `sudo apt update && sudo apt upgrade`
  - Install requirements: `sudo apt install curl git php redis unzip`
  - Verify that PHP 7.1+ is installed: `php -v`
  - Install the corresponding `php-xml` and `php-mbstring` packages for your version, e.g:
    - PHP 7.1: `sudo apt install php7.1-xml php7.1-mbstring`
    - PHP 7.3: `sudo apt install php7.3-xml php7.3-mbstring`
  - Install composer: `curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer`
2. Start the redis server: `sudo service redis start`

#### 01B. Mac

1. Install [brew](https://brew.sh/)
2. Install requirements: `brew install php composer redis`
3. Start the redis server: `brew services start redis`

### 02. Installation

1. `git clone https://github.com/jikan-me/jikan-rest.git`
2. `cd jikan-rest`
3. `cp .env.dist .env`
5. `composer install` (Make sure Jikan's directory has write permissions)

### 03. Configuration

You're able to configure Jikan through the `.env` file.
A few kernel commands are available from the project directory by running the `artisan` file.

The first thing you need to do is generate an `APP_KEY`. 

1. `php artisan key:generate`
2. [Configure how Jikan caches](https://github.com/jikan-me/jikan-rest#05-configuring-how-jikan-caches-optional)
3. [Configure how Jikan handles expired cache](https://github.com/jikan-me/jikan-rest#06-configuring-how-jikan-handles-expired-cache-optional)

### 04. Ignition

`php artisan serve --port=8080` or `php -S localhost:8000 -t public`

Jikan is now hosted on `http://localhost:8000/v3/`

**Alternatively,** host it on Apache (or Nginx)

Create a virtual host and point it to `/public`. Jikan supports Apache out of the box, you just need to create a virtual host and point it to `/public`, and enable the rewrite module for .htaccess (`sudo a2enmod rewrite`), and configure `/etc/apache/apache2.conf` by setting `AllowOverride None` to `AllowOverride All` for the `/var/www` directory.

:information_source: If you wish to configure it for Nginx or anything else, you'll have to port the rewrite rules located at `public/.htaccess`

### 05. Configuring how Jikan Caches (optional)
Jikan caches on file by default in `/storage/framework/cache`. So even if you don't change the caching method, Jikan will work out of the box.

However, you can configure Jikan to cache on Redis instead: `php artisan cache:driver redis`

Note: If you're currently running Jikan, you're required to stop it before running the above command.

### 06. Configuring how Jikan handles expired cache (optional)
Jikan handles cache in the `legacy` manner out of the box. This method was used previously to update cache.


#### 06A. Cache Method: Legacy
When a cache expires, it gets deleted. So if you make a request that has an expired cache, your request will take longer as Jikan has to fetch and parse the new data from MyAnimeList again.

#### 06B. Cache Method: Queue
This is a newly introduced caching method to the API, it's what the public API runs on as well. It requires some further setup.

When a cache expires, it does not get deleted. Instead, if you make a request that has an expired cache, a job will be dispatched to the queue which handles updating the cache in the background. Therefore, the request will keep on providing stale cache until the job is complete and the cache is replaced with fresh data.

This method provides zero delay, and is highly recommended if you have immense traffic coming your way.

:information_source: Note: If you're currently running Jikan, you're required to stop it before running the above command. You're also required to clear any cache you've stored as well as anything on the Redis server.

1. `php artisan cache:method queue`

Next, you need to make sure that there's a service looking after the queue. This can be manually done by running a process through `php artisan queue:work --queue=high,low`. You can set the command to run on cron, nohup, etc.

But a recommended way is to install Supervisor and have it handle the queue automatically.

:information_source: Note: `--queue=high,low`; Jikan stores two types of queues; high priority and low priority. This depends on the type of request. You can check which request is considered to be high priority in the [JikanResponseHandler.php](https://github.com/jikan-me/jikan-rest/blob/master/app/Http/Middleware/JikanResponseHandler.php) middleware in the `HIGH_PRIORITY_QUEUE` array.

:information_source: Note 2: Not all requests are queuable. Some are handled the `legacy` way. You can find out which ones in the [JikanResponseHandler.php](https://github.com/jikan-me/jikan-rest/blob/master/app/Http/Middleware/JikanResponseHandler.php) middleware in the `NON_QUEUEABLE` array.

This reason for this is quite simple. User related requests such as anime/manga list can be frequently updated. They're cached by default for 5 minutes (you can change this in `.env`). But if they were to get queued for a cache update, it would take longer than 5 minutes because the update job would have to wait in line. So it skips the queue and is automatically updated on the request. This does mean a slight delay in fetching and parsing the fresh data from MyAnimeList.

:information_source: Note 3: Note 1 & Note 2 are default behavior. You can obviously change them as per your needs.

##### Configuring Supervisord

###### Linux
1. Install supervisor
    - Linux: `sudo apt install supervisor`
    - Mac: `brew install supervisor`
2. `sudo cp conf/supervisor/jikan-worker.conf /etc/supervisor/conf.d`
    - A default supervisor configureation file is available in this repo `conf/supervisor/jikan-worker.conf`
    - Be sure to update to the correct directory in `jikan-worker.conf` for `command` and `stdout_logfile` to the directory of jikan!
    
Example: If I install Jikan in `/var/www/jikan-is-installed-here`, you will have to adjust the following values in the `jikan-worker.conf` file.
```
...
command=php /var/www/jikan-is-installed-here/artisan queue:work --queue=high,low
...
stdout_logfile=/var/www/jikan-is-installed-here/storage/logs/worker.log
stderr_logfile=/var/www/jikan-is-installed-here/storage/logs/worker.error.log
```

3. `sudo supervisorctl reread`
4. `sudo supervisorctl update`
5. `sudo supervisorctl start jikan-worker:*`

###### Mac
1. Install Supervisor: `brew install supervisor`
2. `supervisord -c /usr/local/etc/supervisord.ini`
3. Copy `conf/supervisor/jikan-worker.conf` to `/usr/local/etc/supervisor.d/`
4. `brew services start supervisor`
5. `sudo supervisorctl update`
6. `sudo supervisorctl start jikan-worker:*`


## Troubleshooting
Please read the [troubleshooting guide](https://github.com/jikan-me/jikan-rest/blob/master/TROUBLESHOOTING.md).
For any additional help, join our [Discord server](https://discord.gg/4q8E4Gg).

## Artisan Commands
Please read the [commands guide](https://github.com/jikan-me/jikan-rest/blob/master/COMMANDS.MD).
For any additional help, join our [Discord server](https://discord.gg/4q8E4Gg).

## Information
If you don't want to host your instance, there's a public API available.

- **[REST DOCUMENTATION](https://jikan.docs.apiary.io)**
- **[Apps/Projects using JikanREST](https://jikan.moe/showcase)**

## Wrappers
- **[.NET]** [Jikan.net](https://github.com/Ervie/jikan.net) by Ervie
- **[Python]** [JikanPy](https://github.com/AWConant/jikanpy) by Andrew Conant & Abhinav Kasamsetty
- **[Ruby]** [Jikan.rb](https://github.com/Zerocchi/jikan.rb) by Zerocchi
- **[JavaScript]** [JikanJS](https://github.com/zuritor/jikanjs) by Zuritor
- **[Java]** [Jikan4java](https://github.com/Doomsdayrs/Jikan4java) by Doomsdayrs
- **[PHP]** [jikan-php](https://github.com/janvernieuwe/jikan-jikanPHP) by Jan Vernieuwe
- **[Node.js]** [jikan-node](https://github.com/xy137/jikan-node) by xy137
- **[Dart]** [jikan-dart](https://github.com/charafau/jikan-dart) by Rafal Wachol
- **[TypeScript]** [jikants](https://github.com/Julien-Broyard/jikants) by Julien Broyard
- **[TypeScript]** [jikan-client](https://github.com/javi11/jikan-client) by Javier Blanco
- **[Go]** [jikan-go](https://github.com/darenliang/jikan-go) by Daren Liang

[Add your wrapper here](https://github.com/jikan-me/jikan/edit/master/readme.md)

## Running Tests

`php vendor/bin/phpunit tests`

Note: Tests may fail due to rate limit from MyAnimeList (HTTP 429)

---
## Backers

## Sugoi (すごい) Backers
Thank you to all our **Sugoi (すごい)** backers! 🙏 [[Become a sugoi backer](https://patreon.com/jikan)]
- [Jared Allard (jaredallard)](https://github.com/jaredallard)
- [hugonun (hug_onun)](https://twitter.com/hug_onun)

## Backers
Thank you to all our backers! 🙏 [[Become a backer](https://patreon.com/jikan)]
- [PurplePinapples](https://github.com/purplepinapples/)
- [Barkdoll (Jesse)](https://github.com/barkdoll/)
- [Piotr Szymczak (Drutol)](https://github.com/Drutol)
- [Jason Weatherly (jamesthebard)](https://twitter.com/jamesthebard)
- [Cesar Irad Mendoza (aberuwu)](https://github.com/aberuwu)

## Sponsors
Thank you to all our sponsors! [[Become a sponsor](https://patreon.com/jikan)]
- [Hibiki Matsujo](https://github.com/assintates)

---

## DISCLAIMER
- Jikan is not affiliated with MyAnimeList.net 
- You are responsible for the usage of this API. Please be respectful towards MyAnimeList's [Terms Of Service](https://myanimelist.net/about/terms_of_use)

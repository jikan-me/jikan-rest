![Jikan](http://i.imgur.com/ctoJ3Jp.png)
# Jikan - Unofficial MyAnimeList.net REST API
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Percentage of issues still open") [![stable](https://img.shields.io/badge/PHP-^%207.1-blue.svg?style=flat)]() [![Discord Server](https://img.shields.io/discord/460491088004907029.svg?style=flat&logo=discord)](https://discord.gg/4q8E4Gg)

Jikan is a REST API for [MyAnimeList.net](https://myanimelist.net). It scrapes the website to satisfy the need for an API - which MyAnimeList lacks.

The raison d'être of Jikan is to assist developers easily get the data they need for their apps and projects without having to depend on the lackluster official API, unstable APIs, or sidetracking their projects to develop parsers.

The word _Jikan_ literally translates to _Time_ in Japanese (**時間**). And that's what this API saves you of. ;)


**Notice**: Jikan does not support authenticated requests. You can not update your lists.

## Getting Started

### Requirements

- PHP 7.1+
- [Composer](https://getcomposer.org/download/)
- [Redis](https://redis.io)

### Installation


#### 🐳 Docker
- [JikanREST Docker](https://github.com/fethica/jikan-rest-docker) by Fethi El Hassasna

#### Linux

This is specifically for Ubuntu, but other distributions should work similarly.

1. Install requirements:
  - Add PHP related packages: `sudo add-apt-repository -y ppa:ondrej/php`
    - If `add-apt-repository` is not installed, you can install it by doing `sudo apt install python-software-properties` or `sudo apt install software-properties-common`
  - `sudo apt update && sudo apt upgrade`
  - Install requirements: `sudo apt install curl git php redis-server unzip supervisor`
  - Verify that PHP 7.1+ is installed: `php -v`
  - Install the corresponding `php-xml` and `php-mbstring` packages for your version, e.g:
    - PHP 7.1: `sudo apt install php7.1-xml php7.1-mbstring`
    - PHP 7.3: `sudo apt install php7.3-xml php7.3-mbstring`
  - Install composer: `curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer`
2. Start the redis server: `sudo service redis start`

#### Mac

1. Install [brew](https://brew.sh/)
2. Install requirements: `brew install php composer redis supervisor`
3. Start the redis server: `brew services start redis`

### Setting Up

1. `git clone https://github.com/jikan-me/jikan-rest.git`
2. `cd jikan-rest`
3. `cp .env.dist .env`
5. `composer install`

#### Configuring Jikan

You're able to configure Jikan through the `.env` file.

Before running, you need to set the correct values to the following keys in `.env`

1. `APP_KEY` : a random 32 alphanumeric character string
2. `APP_URL` : If you're using a virtual host, set the correct domain here. Additionally, include the port number if you've changed it from the standard. **Do not leave a trailing slash.**

##### Do's

- `http://api.jikan.moe` :heavy_check_mark:
- `https://api.jikan.local` :heavy_check_mark:
- `http://localhost` :heavy_check_mark:
- `http://localhost:8000` :heavy_check_mark:

##### Dont's
- `http://localhost/` :x:
- `http://api.jikan.moe/` :x:


#### Configuring Supervisor

##### Linux
1. `sudo cp conf/supervisor/jikan-worker.conf /etc/supervisor/conf.d`
    - Be sure to update to the correct directory in `jikan-worker.conf` for `command` and `stdout_logfile` to the directory of jikan!
2. `sudo supervisorctl reread`
3. `sudo supervisorctl update`
4. `sudo supervisorctl start jikan-worker:*`

##### Mac
1. `supervisord -c /usr/local/etc/supervisord.ini`
2. Copy `conf/supervisor/jikan-worker.conf` to `/usr/local/etc/supervisor.d/`
3. `brew services start supervisor`
4. `sudo supervisorctl update`
5. `sudo supervisorctl start jikan-worker:*`

#### Ignition

To start the server, create a virtual host and point it to `/public`:

`php -S localhost:8000 -t public`

Jikan is now hosted on `http://localhost:8000/v3/`

:information_source: You can also configure Jikan for Apache. If you wish to configure it for Nginx or anything else, you'll have to port the rewrite rules located at `public/.htaccess`

### Information
If you don't want to host your instance, there's a public API available.

- **[REST DOCUMENTATION](https://jikan.docs.apiary.io)**
- **[Apps/Projects using JikanREST](https://jikan.moe/showcase)**

### Wrappers
- **[.NET]** [Jikan.net](https://github.com/Ervie/jikan.net) by Ervie
- **[Python]** [JikanPy](https://github.com/AWConant/jikanpy) by Andrew Conant & Abhinav Kasamsetty
- **[Ruby]** [Jikan.rb](https://github.com/Zerocchi/jikan.rb) by Zerocchi
- **[JavaScript]** [JikanJS](https://github.com/zuritor/jikanjs) by Zuritor
- **[Java]** [Jikan4java](https://github.com/Doomsdayrs/Jikan4java) by Doomsdayrs
- **[PHP]** [jikan-php](https://github.com/janvernieuwe/jikan-jikanPHP) by Jan Vernieuwe
- **[Node.js]** [jikan-node](https://github.com/xy137/jikan-node) by xy137
- **[Dart]** [jikan-dart](https://github.com/charafau/jikan-dart) by Rafal Wachol
- **[TypeScript]** [jikants](https://github.com/Julien-Broyard/jikants) by Julien Broyard

[Add your wrapper here](https://github.com/jikan-me/jikan/edit/master/readme.md)

### Running Tests

`php vendor/bin/phpunit tests`

Note: Tests may fail due to rate limit from MyAnimeList (HTTP 429)

---

# Backers
Thank you to all our backers! 🙏 [[Become a backer](https://patreon.com/jikan)]
- [PurplePinapples](https://github.com/purplepinapples/)
- [Barkdoll (Jesse)](https://github.com/barkdoll/)

# Sponsors
Thank you to all our sponsors! You will recieve advertisement/promotive perks. [[Become a sponsor](https://patreon.com/jikan)]
- [Hibiki Matsujo](https://github.com/assintates)

---

## DISCLAIMER
- Jikan is not affiliated with MyAnimeList.net 
- You are responsible for the usage of this API. Please be respectful towards MyAnimeList's [Terms Of Service](https://myanimelist.net/about/terms_of_use)

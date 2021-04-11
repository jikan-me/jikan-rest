![Jikan](http://i.imgur.com/ctoJ3Jp.png)
# Jikan - Unofficial MyAnimeList.net REST API
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Percentage of issues still open") [![stable](https://img.shields.io/badge/PHP-^7.2.5-blue.svg?style=flat)]() [![Discord Server](https://img.shields.io/discord/460491088004907029.svg?style=flat&logo=discord)](https://discordapp.com/invite/4tvCr36)

Jikan is a REST API for [MyAnimeList.net](https://myanimelist.net). It scrapes the website to satisfy the need for some API functionality - that MyAnimeList lacks.

The raison d'être of Jikan is to assist developers easily get the data they need for their apps and projects without having to depend on unstable APIs, or sidetracking their projects to develop parsers.

The word _Jikan_ literally translates to _Time_ in Japanese (**時間**). And that's what this API saves you of. ;)

**Notice**: Jikan does not support authenticated requests. You can not update your lists. Use the official MyAnimeList API for this.

## Index
- [What's new?](#whats-new)
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

## What's new?

## Getting Started

### Requirements

- PHP ^7.4
- [Composer](https://getcomposer.org/download/)
- [MongoDB]()



## Information
If you don't want to host your instance, there's a public API available.

- **[REST DOCUMENTATION](http://docs.api.jikan.moe/)**
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
- **[Elixir]** [JikanEx](https://github.com/seanbreckenridge/jikan_ex) by Sean Breckenridge

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

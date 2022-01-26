[![Jikan](https://i.imgur.com/ccx3pxo.png)](#jikan-rest-api-v4---unofficial-myanimelistnet-rest-api)

# Jikan REST API v4 - Unofficial MyAnimeList.net REST API
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Percentage of issues still open") [![stable](https://img.shields.io/badge/PHP-^8.0-blue.svg?style=flat)]() [![Discord Server](https://img.shields.io/discord/460491088004907029.svg?style=flat&logo=discord)](https://discordapp.com/invite/4tvCr36)

Jikan is a REST API for [MyAnimeList.net](https://myanimelist.net). It scrapes the website to satisfy the need for API functionality that MyAnimeList.net lacks.

The raison d'être of Jikan is to assist developers easily get the data they need for their apps and projects without having to depend on unstable APIs, or sidetracking their projects to develop parsers.

The word _Jikan_ literally translates to _Time_ in Japanese (**時間**). And that's what this API saves you of. ;)

**Notice**: Jikan REST API does not support authenticated requests. You can not update your lists. Use the official MyAnimeList API for this!


## Installation

### NOTICE: v3
This is Jikan REST API **v4** - which has been merged into master branch. If you're looking for deploying a v3 build, view the v3 branch for installation instructions:
https://github.com/jikan-me/jikan-rest/tree/v3

⚠ v3 is now maintaince-only and will stop receiving updates once it's discontinued from the public API.

### Manual installation
Please read the [manual installation guide](https://github.com/jikan-me/jikan-rest/wiki).
For any additional help, join our [Discord server](http://discord.jikan.moe/).

### 🐳 Docker Installation
If you don't want to install it manually, you can use the [docker image](https://github.com/jikan-me/jikan-docker)

## Public REST API
If you don't want to host your instance, there's a public API available.

- *[Apps/Projects using the REST API](https://jikan.moe/showcase)*

### Documentation
Please view the [documentation](https://docs.api.jikan.moe/).
For any additional help, join our [Discord server](http://discord.jikan.moe/).

## Wrappers

| Language   | Wrappers |
|------------|----------|
| JavaScript | [JikanJS](https://github.com/zuritor/jikanjs) by Zuritor |
| Java       | [Jikan4java](https://github.com/Doomsdayrs/Jikan4java) by Doomsdayrs<br>[reactive-jikan](https://github.com/SandroHc/reactive-jikan) by Sandro Marques<br>[Jaikan](https://github.com/ShindouMihou/Jaikan) by ShindouMihou |
| Python     | [JikanPy](https://github.com/abhinavk99/jikanpy) by Abhinav Kasamsetty |
| Node.js    | [jikan-node](https://github.com/xy137/jikan-node) by xy137<br>[jikan-nodejs](https://github.com/ribeirogab/jikan-nodejs) by ribeirogab |
| TypeScript | [jikants](https://github.com/Julien-Broyard/jikants) by Julien Broyard<br>[jikan-client](https://github.com/javi11/jikan-client) by Javier Blanco |
| PHP        | [jikan-php](https://github.com/janvernieuwe/jikan-jikanPHP) by Jan Vernieuwe |
| .NET       | [Jikan.net](https://github.com/Ervie/jikan.net) by Ervie |
| Elixir     | [JikanEx](https://github.com/seanbreckenridge/jikan_ex) by Sean Breckenridge |
| Go         | [jikan-go](https://github.com/darenliang/jikan-go) by Daren Liang<br>[jikan2go](https://github.com/nokusukun/jikan2go) by nokusukun |
| Ruby       | [Jikan.rb](https://github.com/Zerocchi/jikan.rb) by Zerocchi |
| Dart       | [jikan-dart](https://github.com/charafau/jikan-dart) by Rafal Wachol |
| Kotlin     | [JikanKt](https://github.com/GSculerlor/JikanKt) by Ganedra Afrasya |

[Add your wrapper here](https://github.com/jikan-me/jikan-rest/edit/master/readme.md)

## Running Tests

`php vendor/bin/phpunit tests`

Note: Tests may fail due to rate limit from MyAnimeList (HTTP 429)

---
## Backers

A huge thank you to all our Patrons! 🙏 This project wouldn't be running without your support.

We have a free [REST API service](https://jikan.moe), if you wish to support us you can [become a Patron!](https://patreon.com/jikan)

### Sugoi (すごい) Patrons

- [Jared Allard (jaredallard)](https://github.com/jaredallard)
- [hugonun (hug_onun)](https://twitter.com/hug_onun)

### Patrons

- Aaron Treinish
- Aika Fujiwara
- Cesar Irad Mendoza
- Fro116
- Jason Weatherly
- Jesse
- Kundan Chintamaneni
- Kururin
- Purplepinapples
- Ryo Ando
- Sakamotodesu
- TeraNovaLP

## Development

|||
|------------|----------|
| ![JetBrain](https://user-images.githubusercontent.com/9166451/126047249-9e5bdc63-ae91-4082-bca5-ffe271b421da.png) | Jikan's development is powered by [JetBrain's Open Source License](https://jb.gg/OpenSource) |

A shoutout to their amazing products and for supporting Jikan since early versions!

---

# DISCLAIMER

- Jikan is not affiliated with MyAnimeList.net
- You are responsible for the usage of this API. Please be respectful towards MyAnimeList's [Terms Of Service](https://myanimelist.net/about/terms_of_use)

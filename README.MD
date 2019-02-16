![Jikan](http://i.imgur.com/ctoJ3Jp.png)
# Jikan - Unofficial MyAnimeList.net REST API
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/jikan-me/jikan-rest.svg)](http://isitmaintained.com/project/jikan-me/jikan-rest "Percentage of issues still open") [![stable](https://img.shields.io/badge/PHP-^%207.1-blue.svg?style=flat)]() [![Discord Server](https://img.shields.io/discord/460491088004907029.svg?style=flat&logo=discord)](https://discord.gg/4q8E4Gg)

Jikan is a REST API for [MyAnimeList.net](https://myanimelist.net). It scrapes the website to satisfy the need for an API - which MyAnimeList lacks.

The raison d'être of Jikan is to assist developers easily get the data they need for their apps and projects without having to depend on the lackluster official API, unstable APIs, or sidetracking their projects to develop parsers.

The word _Jikan_ literally translates to _Time_ in Japanese (**時間**). And that's what this API saves you of. ;)


**Notice**: Jikan does not support authenticated requests. You can not update your lists.

## Getting Started

### Requirements
- [Composer](getcomposer.org/download/)
- [Redis](https://redis.io)
- PHP 7.1+

### Installation

1. `git clone https://github.com/jikan-me/jikan-rest.git`
2. Rename `.env.dist` to `.env`
3. `composer install`
4. **Optional** Create a virtual host and point it to `/public`

## Information
If you don't want to host your instance, there's a public API available.

- **[REST DOCUMENTATION](https://jikan.docs.apiary.io)**
- **[Apps/Projects using JikanREST](https://jikan.moe/showcase)**

### Available Wrappers
- **[.NET]** [Jikan.net](https://github.com/Ervie/jikan.net) by Ervie
- **[Python]** [JikanPy](https://github.com/AWConant/jikanpy) by Andrew Conant & Abhinav Kasamsetty
- **[Ruby]** [Jikan.rb](https://github.com/Zerocchi/jikan.rb) by Zerocchi
- **[JavaScript]** [JikanJS](https://github.com/zuritor/jikanjs) by Zuritor
- **[Java]** [Jikan4java](https://github.com/Doomsdayrs/Jikan4java) by Doomsdayrs
- **[PHP]** [jikan-php](https://github.com/janvernieuwe/jikan-jikanPHP) by Jan Vernieuwe
- **[Node.js]** [jikan-node](https://github.com/xy137/jikan-node) by xy137

[Add your own wrapper here](https://github.com/jikan-me/jikan/edit/master/readme.md)

## DISCLAIMER
- Jikan is not affiliated with MyAnimeList.net 
- You are responsible for the usage of this API. Please be respectful towards MyAnimeList's [Terms Of Service](https://myanimelist.net/about/terms_of_use)

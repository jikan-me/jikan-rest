# Security Policy

## Supported versions

| Version | Supported |
| ------- | --------- |
| 4.3.x   | Yes       |
| < 4.3   | No — upgrade to 4.3.x (see [docs/UPGRADING.md](docs/UPGRADING.md)) |

Security fixes are released as new 4.3.x patch versions.

## Reporting a vulnerability

**Do not open a public issue, pull request or discussion for a vulnerability.**

Report it privately through GitHub's private vulnerability reporting:
go to the [Security tab of jikan-me/jikan-rest](https://github.com/jikan-me/jikan-rest/security)
and choose **Report a vulnerability** (this opens a draft Security Advisory that only the
maintainers can see).

Please include:

- the affected version (release tag or commit) and how it is deployed (Docker image, compose, bare metal);
- the affected endpoint, command or file;
- steps to reproduce or a proof of concept, and the impact you expect;
- any relevant configuration (`.env` keys, cache/queue/search drivers) — never real secrets;
- a suggested fix, if you have one.

## What to expect

- An acknowledgement within a few days.
- An assessment and, if confirmed, a fix plan; we will keep you updated in the advisory.
- Once a fix is released we publish the advisory and credit you, unless you prefer otherwise.

Please give us a reasonable time to release a fix before any public disclosure.

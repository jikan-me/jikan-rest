#!/usr/bin/env bash

export PHP_OWNER=nekomata1037
export PHP_REPO=jikan-rest-php
export PHP_TAG=2018-11-08
export PHP_IMAGE=${PHP_OWNER}/${PHP_REPO}:${PHP_TAG}

export WEB_ACCOUNT=nekomata1037
export WEB_REPO=jikan-rest-web
export WEB_TAG=2018-11-08
export WEB_IMAGE=${WEB_ACCOUNT}/${WEB_REPO}:${WEB_TAG}

export MYSQL_ACCOUNT=library
export MYSQL_REPO=mysql
export MYSQL_TAG=5.6.29
export MYSQL_IMAGE=${MYSQL_ACCOUNT}/${MYSQL_REPO}:${MYSQL_TAG}

export SED_REPO=alpine
export SED_TAG=edge

export REDIS_REPO=redis
export REDIS_TAG=4.0.10
export REDIS_IMAGE=${REDIS_REPO}:${REDIS_TAG}

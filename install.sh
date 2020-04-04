#!/bin/sh

docker-compose up -d --build

docker-compose run php composer install

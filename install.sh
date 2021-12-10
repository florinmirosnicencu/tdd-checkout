#!/bin/bash

docker-compose up -d --build

docker-compose run php composer install

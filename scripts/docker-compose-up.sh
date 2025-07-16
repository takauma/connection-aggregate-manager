#!/bin/sh

docker compose up --build -d
docker compose exec app sh /init.sh
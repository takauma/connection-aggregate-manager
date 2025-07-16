#!/bin/sh

docker compose restart
docker compose exec app sh /init.sh
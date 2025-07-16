docker compose down
docker image rm connection-aggregate-manager-app
docker image rm connection-aggregate-manager-nginx
docker volume rm cam-node_modules
docker volume rm cam-vendor
#!/bin/bash

rm -rf /src/storage
rm -rf /src/node_modules
rm -rf /src/vendor
rm -rf /src/public/hot
mkdir -p /src/storage/framework/cache/data
mkdir -p /src/storage/framework/views
chmod -R guo+w /src/storage
composer install
npm install
npm run build
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
php artisan clear-compiled
php artisan optimize
php artisan config:cache
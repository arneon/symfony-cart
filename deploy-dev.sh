#!/bin/bash

set -euo pipefail

cp ./.env.example ./.env
cp ./symfony/.env.example ./symfony/.env
cp ./symfony/.env.test.example ./symfony/.env.test
cp ./symfony/.env.test.local.example ./symfony/.env.test.local

git pull

cd symfony
composer install

cd ..

make up
make down

make up
make migrate

cd symfony
php bin/phpunit --testdox --colors
XDEBUG_MODE=coverage php bin/phpunit --coverage-html public/coverage
#!/bin/bash

set -euo pipefail

./set-env.sh
exit 0
git pull

cd symfony
composer install
php bin/console lexik:jwt:generate-keypair --overwrite

cd ..

make up
make down

make up
make migrate

cd symfony
php bin/phpunit --testdox --colors
XDEBUG_MODE=coverage php bin/phpunit --coverage-html public/coverage
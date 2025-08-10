include .env

PHP_CONTAINER=$(PHP_CONTAINER_NAME)
REDIS_CONTAINER=$(REDIS_CONTAINER_NAME)
NGINX_CONTAINER=$(NGINX_CONTAINER_NAME)
MYSQL_CONTAINER=$(MYSQL_CONTAINER_NAME)

WORKDIR=/var/www/html

up:
	docker-compose up -d --build

down:
	docker-compose down

restart: down up

bash:
	docker exec -it $(PHP_CONTAINER) bash

redis:
	docker exec -it $(REDIS_CONTAINER) redis-cli

composer-install:
	docker exec $(PHP_CONTAINER) composer install

cache-clear:
	docker exec $(PHP_CONTAINER) composer dump-autoload
	docker exec $(PHP_CONTAINER) php bin/console cache:clear

logs:
	docker-compose logs -f

logs-nginx:
	docker logs -f $(NGINX_CONTAINER)

logs-php:
	docker logs -f $(PHP_CONTAINER)

logs-mysql:
	docker logs -f $(MYSQL_CONTAINER)

migrate:
	docker exec $(PHP_CONTAINER) php bin/console doctrine:migrations:diff --quiet  > /dev/null 2>&1
	docker exec $(PHP_CONTAINER) php bin/console doctrine:migrations:migrate --no-interaction --quiet


all:
	docker compose run --rm php composer qa

test: vendor/autoload.php
	docker compose run --rm php composer test

vendor/autoload.php:
	docker compose run --rm --no-deps php composer install

clean:
	docker compose down --volumes --remove-orphans

build:
	docker compose run --rm php composer build

sign:
	gpg --detach-sign --armor --interactive dbdatool.phar

all-versions-test:
	env PHP_VERSION=7.2 docker-compose run --rm php composer update
	env PHP_VERSION=7.2 docker-compose run --rm php composer test
	env PHP_VERSION=7.3 docker-compose run --rm php composer test
	env PHP_VERSION=7.4 docker-compose run --rm php composer test
	env PHP_VERSION=8.0 docker-compose run --rm php composer test
	env PHP_VERSION=8.1 docker-compose run --rm php composer test


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

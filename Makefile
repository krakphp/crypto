.PHONY: composer test

PERIDOT = ./vendor/bin/peridot

composer:
	composer update

test:
	$(PERIDOT) --grep='test/*.php'

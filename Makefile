.PHONY: composer test

ifndef dest
    dest=docs/api
endif

PERIDOT = ./vendor/bin/peridot

composer: composer.phar
	./composer.phar update

test:
	$(PERIDOT) test

api: apigen.phar
	./apigen.phar generate --title 'Krak Crypto' -s src -d $(dest)

composer.phar:
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php -r "if (hash_file('SHA384', 'composer-setup.php') === '92102166af5abdb03f49ce52a40591073a7b859a86e8ff13338cf7db58a19f7844fbc0bb79b2773bf30791e935dbd938') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	php composer-setup.php
	php -r "unlink('composer-setup.php');"

apigen.phar:
	wget http://apigen.org/apigen.phar
	chmod +x apigen.phar

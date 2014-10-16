#!/usr/bin/env bash

# https://github.com/travis-ci/travis-ci/issues/2523

PHP_VER=$(phpenv version-name)

if [[ $PHP_VER == "hhvm" ]]; then
	sudo sh -c 'echo "extension = mongo.so" >> /etc/hhvm/php.ini'
	# memcache and memcached are enabled by default
else
	echo "extension = mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
	echo "extension = memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
	echo "extension = memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi
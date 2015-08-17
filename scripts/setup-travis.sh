#!/bin/sh

is_hhvm=$(echo "$TRAVIS_PHP_VERSION" | grep hhvm$)

if [ ! $is_hhvm ]; then
	ini_path=~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
	echo "extension = mongo.so" >> $ini_path
	echo "extension = memcache.so" >> $ini_path
	echo "extension = memcached.so" >> $ini_path
fi

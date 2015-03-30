#!/usr/bin/env bash

set -ev

if [ "$TRAVIS_PHP_VERSION" == *"hhvm"* ]; then
	INI_PATH=~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
	echo "extension = mongo.so" >> $INI_PATH
	echo "extension = memcache.so" >> $INI_PATH
	echo "extension = memcached.so" >> $INI_PATH
fi

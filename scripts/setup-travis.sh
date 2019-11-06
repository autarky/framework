#!/bin/sh

is_hhvm=$(echo "$TRAVIS_PHP_VERSION" | grep hhvm$)

if [ ! $is_hhvm ]; then
	php_path=~/.phpenv/versions/$(phpenv version-name)
	ini_path=$php_path/etc/php.ini
	ext_path=$php_path/lib/php/extensions
	for ext in mongo memcache memcached; do
		if find ext_path -name $ext.so | grep -q '.*'; then
			echo "extension = $ext" >> $ini_path
		fi
	done
fi

# Autarky [![Build Status](https://travis-ci.org/autarky/framework.png?branch=master)](https://travis-ci.org/autarky/framework) [![Latest Version](http://img.shields.io/github/tag/autarky/framework.svg)](https://github.com/autarky/framework/releases)

Read the [wiki](https://github.com/autarky/framework/wiki) for more information.

Try the framework out by creating a skeleton project using composer.

If you don't have composer installed already:

```
curl -sS https://getcomposer.org/installer | php
chmod +x composer.phar && mv composer.phar /usr/local/bin/composer
```

Create the skeleton project using composer:

```
composer create-project -s dev autarky/skeleton --prefer-dist ./myproject
cd myproject && php -S localhost:8000 -t ./public
```

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).

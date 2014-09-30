# Autarky [![Build Status](https://travis-ci.org/autarky/framework.png?branch=master)](https://travis-ci.org/autarky/framework) [![Latest Version](http://img.shields.io/github/release/autarky/framework.svg)](https://github.com/autarky/framework/releases)
[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/autarky/framework?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Autarky is a PHP framework for experienced developers and/or quick learners. Read the [wiki](https://github.com/autarky/framework/wiki) for more information.

### Installation

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

Visit the url "localhost:8000" in your browser to make sure it's working.

### Contributing

```
git clone https://github.com/autarky/framework /path/to/autarky
cd /path/to/autarky
./vendor/bin/phpunit
```

The master branch is the current minor version. Previous minor versions have their own branches. Only critical bugfixes should be applied to these branches.

The develop branch is the next minor version. New features are applied to this branch.

Read the [CONTRIBUTING.md](CONTRIBUTING.md) file for more information.

### License

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT). See the [LICENSE](LICENSE) file included for more information.

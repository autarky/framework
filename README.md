# Autarky

[![Build Status](https://travis-ci.org/autarky/framework.png?branch=master)](https://travis-ci.org/autarky/framework)
[![Latest Stable Version](https://poser.pugx.org/autarky/framework/v/stable.svg)](https://github.com/autarky/framework/releases)
[![Latest Unstable Version](https://poser.pugx.org/autarky/framework/v/unstable.svg)](https://github.com/autarky/framework/branches/active)
[![License](https://poser.pugx.org/autarky/framework/license.svg)](http://opensource.org/licenses/MIT)

Autarky is a PHP framework for experienced developers and/or quick learners, with a focus on developer freedom of choice, configuration over convention and the right mix of rapid/pleasant development and sturdy application architecture.

Documentation is available in the [wiki](https://github.com/autarky/framework/wiki). [API docs](http://autarky.lutro.me/api/) are also available.

Changelog and upgrade instructions are available in Github's [releases](https://github.com/autarky/framework/releases).

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

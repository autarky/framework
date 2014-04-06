# Autarky

A minimalist framework designed for the right mix of flexibility, speed of development and clean object oriented programming. 

Currently a work in progress - none of the class names, method names or naming conventions should be considered stable, so use at your own risk - but if you want to help shape a new framework, please put your ideas, thoughts and concerns in the github issues!

Features include:

- Automatic dependency injection via typehinting in constructor parameters
- Utilizes Symfony's HttpFoundation for easy handling of responses and requests as well as the session
- Comes with an implementation of nikic's FastRoute, a very performant router
- Comes with Twig, the most robust, extensible templating engine available in PHP
- Interfaces for the router, templating engine, IoC container and config loader means you can easily swap out the default implementations with your own
- Service providers - easily add your own classes to the IoC container and configure the application in a modular fashion - makes for great package development
- Namespaced resources - shared namespace system between templates, config files and more to make it easy to split your application up or write re-usable packages
- Testable - comes with an abstract TestCase that makes system-level/functional testing a breeze

The framework does not come with a database layer, mail library, cache layer, authentication service, queue services, translation service and so on. Feel free to pick whichever of these you like, write a serviceprovider for them and share them with the world.


### Creating a project

This repository contains the framework's core classes and tests. To create a new project using Autarky, I suggest using [Composer](https://getcomposer.org/):

`composer create-project autarky/skeleton --prefer-dist /path/to/project`

This will set up a minimalist project for you to build on top of. Keep in mind that during version 0.x a lot of breaking changes will happen.


## Contact

Open an issue on GitHub if you have any problems or suggestions.


## License

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).
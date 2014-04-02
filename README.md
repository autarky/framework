# Autarky

A minimalist framework designed for the right mix of flexibility, speed of development and clean object oriented programming. Features include:

- Automatic dependency injection via typehinting in constructor parameters, utilizing Wart, an extension of Pimple
- Utilizes Symfony's HttpFoundation for easy handling of responses and requests
- Loosely coupled components - comes with Symfony's session handler, a FastRoute implementation and the Twig templating engine out of the box, all of which can be easily replaced
- Service providers - easily add your own classes to the IoC container and configure the application in a modular fashion - makes for great package development
- Testable - comes with an abstract TestCase that makes system-level/functional testing a breeze

The framework does not come with a database layer, mail library, cache layer, authentication service, queue services, translation service and so on. Feel free to pick whichever of these you like, write a serviceprovider for them and share them with the world.


### Creating a project

This repository contains the framework's core classes and tests. To create a new project using Autarky, I suggest using [Composer]():

`composer create-project autarky/skeleton --prefer-dist /path/to/project`

This will set up a minimalist project for you to build on top of.


## Contact

Open an issue on GitHub if you have any problems or suggestions.


## License

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).
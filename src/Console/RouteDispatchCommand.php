<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

class RouteDispatchCommand extends Command
{
	public function configure()
	{
		$this->setName('route:dispatch')
			->setDescription('Given a URL, see what route is matched.')
			->addArgument('method', InputArgument::REQUIRED, 'HTTP method.')
			->addArgument('path', InputArgument::REQUIRED, 'A URL path.')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$router = $this->app->getContainer()
			->resolve('Autarky\Routing\Router');

		$request = Request::create($input->getArgument('path'), $input->getArgument('method'));
		$route = $router->getRouteForRequest($request);

		$controller = $route->getController();

		if (is_array($controller)) {
			$controller = implode('::', $controller);
		} else if ($controller instanceof \Closure) {
			$controller = 'Closure';
		}

		$output->writeln('<info>Route name:</info> '.$route->getName());
		$output->writeln('<info>Route controller:</info> '.$controller);
		$output->writeln('<info>Route path:</info> '.$route->getPattern());
		$output->writeln('<info>HTTP methods:</info> '.implode('|', $route->getMethods()));

		if ($filters = $route->getBeforeFilters()) {
			$output->writeln('<info>Before filters:</info> '.implode(', ', $filters));
		}
		if ($filters = $route->getAfterFilters()) {
			$output->writeln('<info>After filters:</info> '.implode(', ', $filters));
		}
	}
}

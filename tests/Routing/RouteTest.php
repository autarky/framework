<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Tests\Routing;

use PHPUnit_Framework_TestCase;

use Autarky\Routing\Route;

class RouteTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function pathCanBeGenerated()
    {
        $route = new Route(['get'], '/foo/{v1}/{v2}', 'handler');
        $this->assertEquals('/foo/bar/baz', $route->getPath(['bar', 'baz']));
    }
}

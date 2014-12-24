<?php
namespace Autarky\Container\Factory;

interface ArgumentInterface
{
	public function getPosition();
	public function getName();
	public function isRequired();
	public function isClass();
}

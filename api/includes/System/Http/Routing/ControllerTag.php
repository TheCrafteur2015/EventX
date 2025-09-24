<?php

namespace System\Http\Routing;

use Attribute;
use InvalidArgumentException;
use ReflectionAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ControllerTag {
	
	public function __construct(public readonly string $name, public readonly string $description = "") {}
	
	public static function getName(ReflectionAttribute $attr): string {
		if ($attr->getName() !== ControllerTag::class)
			throw new InvalidArgumentException("Not a ControllerTag instance");
		$args = $attr->getArguments();
		if (isset($args['name']))
			return $args['name'];
		if (isset($args[0]))
			return $args[0];
		throw new InvalidArgumentException("Missing name argument");
	}
	
}

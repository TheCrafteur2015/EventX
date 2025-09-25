<?php

namespace System\Event;

use JetBrains\PhpStorm\Immutable;
use ReflectionClass;

#[Immutable]
class Event {
	
	public static function getEvents(): array {
		$oClass = new ReflectionClass(static::class);
		return array_values($oClass->getConstants());
	}
	
	public static function getAllEvents(): array {
		$events = self::getEvents();
		foreach (get_subclasses(self::class) as $subclass)
			$events = array_merge($events, $subclass::getEvents());
		return array_unique($events);
	}
	
	public readonly string $type;
	
	public function __construct(string $type) {
		if (!in_array($type, static::getEvents()))
			trigger_error("Event Listener '$type' does not exist", E_USER_WARNING);
		$this->type = $type;
	}
	
}

<?php

namespace System\Event;

use Closure;

class EventHandler {
	
	private static array $listeners = [];
	
	private function __construct() {}
	
	public static function init(): void {
		foreach (Event::getAllEvents() as $event) {
			self::$listeners[$event] = [];
		}
	}
	
	public static function on(string $event_type, callable $callable): void {
		if (!in_array($event_type, Event::getEvents()))
			trigger_error("Event Listener '$event_type' does not exist", E_USER_WARNING);
		self::$listeners[$event_type][] = $callable;
	}
	
	public static function off(string $event_type, callable $callable = null): void {
		if (!in_array($event_type, Event::getEvents()))
			trigger_error("Event Listener '$event_type' does not exist", E_USER_WARNING);
		if ($callable === null)
			self::$listeners[$event_type] = [];
		if ($callable instanceof Closure && in_array($callable, self::$listeners[$event_type])) {
			$index = array_search($callable, self::$listeners[$event_type]);
			unset(self::$listeners[$event_type][$index]);
		}
	}
	
	private static function getListeners(string $type): array|null {
		if (array_key_exists($type, self::$listeners))
			return self::$listeners[$type];
		return null;
	}
	
	public static function trigger(Event $event): void {
		$listeners = self::getListeners($event->type);
		if ($listeners) {
			foreach ($listeners as $listener) {
				$listener($event);
			}
		}
	}
	
}

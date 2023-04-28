<?php

namespace Freekattema\Tools\Routing;

class Route
{
	private static array $subdomain = [];
	private static $routes = [];

	public static function get($route, $handler)
	{
		self::register(
			method: 'GET',
			route: $route,
			handler: $handler,
		);
	}

	public static function post($route, $handler): void
	{
		self::register(
			method: 'POST',
			route: $route,
			handler: $handler,
		);
	}

	public static function put($route, $handler): void
	{
		self::register(
			method: 'PUT',
			route: $route,
			handler: $handler,
		);
	}

	public static function patch($route, $handler): void
	{
		self::register(
			method: 'PATCH',
			route: $route,
			handler: $handler,
		);
	}

	public static function delete($route, $handler): void
	{
		self::register(
			method: 'DELETE',
			route: $route,
			handler: $handler,
		);
	}

	public static function options($route, $handler): void
	{
		self::register(
			method: 'OPTIONS',
			route: $route,
			handler: $handler,
		);
	}

	public static function any($route, $handler): void
	{
		self::register(
			method: 'GET|POST|PUT|PATCH|DELETE|OPTIONS',
			route: $route,
			handler: $handler,
		);
	}

	public static function custom(array $method, $route, $handler): void
	{
		// Add the route to the array of routes
		self::$routes[] = [
			'method' => implode('|', $method),
			'route' => $route,
			'handler' => $handler,
			'subdomain' => implode('.', self::$subdomain),
		];
	}


	public static function subdomain($subdomain, $callback): void
	{
		self::$subdomain[] = $subdomain;
		$callback();
		array_pop(self::$subdomain);
	}

	public static function matchRoute($routeToMatch, $method = 'GET', $subdomain = '')
	{
		// Define the current route, method, and subdomain
		$currentRoute = $_SERVER['REQUEST_URI'];
		$currentMethod = $_SERVER['REQUEST_METHOD'];
		$currentSubdomain = explode('.', $_SERVER['HTTP_HOST']);
		array_pop($currentSubdomain);
		$currentSubdomain = implode('.', $currentSubdomain);

		$method = explode('|', $method);

		// Check if the method and subdomain match
		if ($currentSubdomain !== $subdomain) {
			return false;
		}

		if (!in_array($currentMethod, $method)) {
			return false;
		}

		// Check if the subdomain matches
		if ($subdomain === 'api') {
			// If the subdomain is "api", remove it from the route to match
			$routeToMatch = substr($routeToMatch, 4);
		}

		// Replace any variables in the route to match with regex patterns
		$routeToMatch = preg_replace('/{([^}]+)}/', '(?P<$1>[^/]+)', $routeToMatch);

		// Create a regex pattern to match the entire route
		$pattern = '#^' . $routeToMatch . '$#';

		// Check if the current route matches the pattern
		$matches = [];
		if (preg_match($pattern, $currentRoute, $matches)) {
			return $matches;
		}

		// If no match found
		return false;
	}

	public static function handleRequest()
	{
		foreach (self::$routes as $route) {
			$matches = self::matchRoute($route['route'], $route['method'], $route['subdomain']);

			if ($matches !== false) {
				$handler = $route['handler'];

				// Extract the matched route variables and call the handler function
				$args = array_map(function ($match) {
					return urldecode($match);
				}, $matches);
				// get only the args that have a non-numeric key
				$args = array_filter($args, function ($key) {
					return !is_numeric($key);
				}, ARRAY_FILTER_USE_KEY);
				call_user_func_array($handler, $args);

				// Exit the function after the first match is found
				return;
			}
		}

		// If no routes matched, return a 404 error
		http_response_code(404);
		echo '404 Not Found';
	}

	private static function register($method, $route, $handler): void
	{
		self::$routes[] = [
			'method' => $method,
			'route' => $route,
			'handler' => $handler,
			'subdomain' => implode('.', self::$subdomain),
		];
	}
}

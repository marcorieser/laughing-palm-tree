<?php

use Symfony\Component\Routing;

$routes = new Routing\RouteCollection();

$routes->add(
		'hello',
		new Routing\Route(
				'/hello/{name}', [
				'name' => 'World',
				'_controller' => 'render_template',
		]));

$routes->add(
		'bye',
		new Routing\Route(
				'/bye', [
				'_controller' => 'render_template',
		]));

$routes->add(
		'overwrite_controller',
		new Routing\Route(
				'/overwrite/{content}', [
				'content' => 'empty',
				'_controller' => static function ($request) {
					return new \Symfony\Component\HttpFoundation\Response(
							'The content is: '.htmlspecialchars($request->attributes->get('content'), ENT_QUOTES, 'UTF-8')
					);
				},
		]));

return $routes;

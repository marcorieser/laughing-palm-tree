<?php

use Simplex\Framework;
use Simplex\StringResponseListener;
use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\Routing;

$containerBuilder = new DependencyInjection\ContainerBuilder();
$containerBuilder->setParameter('routes', include __DIR__.'/../src/app.php');
$containerBuilder->setParameter('charset', 'UTF-8');

$containerBuilder->register('context', Routing\RequestContext::class);
$containerBuilder->register('matcher', Routing\Matcher\UrlMatcher::class)
		->setArguments(['%routes%', new Reference('context')]);
$containerBuilder->register('request_stack', HttpFoundation\RequestStack::class);
$containerBuilder->register('controller_resolver', HttpKernel\Controller\ControllerResolver::class);
$containerBuilder->register('argument_resolver', HttpKernel\Controller\ArgumentResolver::class);

$containerBuilder->register('listener.router', HttpKernel\EventListener\RouterListener::class)
		->setArguments([new Reference('matcher'), new Reference('request_stack')]);

$containerBuilder->register('listener.string_response', StringResponseListener::class);
$containerBuilder->register('matcher', Routing\Matcher\UrlMatcher::class)
		->setArguments(['%routes%', new Reference('context')]);

$containerBuilder->register('listener.response', ResponseListener::class)
		->setArguments(['%charset%']);

$containerBuilder->register('listener.exception', HttpKernel\EventListener\ErrorListener::class)
		->setArguments(['Calendar\Controller\ErrorController::exception']);
$containerBuilder->register('dispatcher', EventDispatcher\EventDispatcher::class)
		->addMethodCall('addSubscriber', [new Reference('listener.router')])
		->addMethodCall('addSubscriber', [new Reference('listener.response')])
		->addMethodCall('addSubscriber', [new Reference('listener.exception')]);

$containerBuilder->getDefinition('dispatcher')
		->addMethodCall('addSubscriber', [new Reference('listener.string_response')]);

$containerBuilder->register('framework', Framework::class)
		->setArguments(
				[
						new Reference('dispatcher'),
						new Reference('controller_resolver'),
						new Reference('request_stack'),
						new Reference('argument_resolver'),
				]);

return $containerBuilder;

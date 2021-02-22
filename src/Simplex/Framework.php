<?php

namespace Simplex;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class Framework implements HttpKernelInterface {
	private EventDispatcher $dispatcher;
	private UrlMatcherInterface $matcher;
	private ControllerResolverInterface $controllerResolver;
	private ArgumentResolverInterface $argumentResolver;

	public function __construct(EventDispatcher $dispatcher, UrlMatcherInterface $matcher, ControllerResolverInterface $controllerResolver, ArgumentResolverInterface $argumentResolver) {
		$this->dispatcher = $dispatcher;
		$this->matcher = $matcher;
		$this->controllerResolver = $controllerResolver;
		$this->argumentResolver = $argumentResolver;
	}

	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
		$this->matcher->getContext()->fromRequest($request);

		try {
			$request->attributes->add($this->matcher->match($request->getPathInfo()));

			$controller = $this->controllerResolver->getController($request);
			$arguments = $this->argumentResolver->getArguments($request, $controller);

			$response = call_user_func_array($controller, $arguments);
		} catch (ResourceNotFoundException $exception) {
			$response = new Response('Not Found', 404);
		} catch (\Exception $exception) {
			$response = new Response('An error occured', 500);
		}

		$this->dispatcher->dispatch(new ResponseEvent($response, $request), 'response');
		return $response;
	}
}

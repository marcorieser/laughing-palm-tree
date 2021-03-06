<?php

namespace Simplex\Tests;

use Calendar\Controller\LeapYearController;
use PHPUnit\Framework\TestCase;
use Simplex\Framework;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

class FrameworkTest extends TestCase {
	public function testNotFoundHandling() {
		$framework = $this->getFrameWorkForException(new ResourceNotFoundException());
		$response = $framework->handle(new Request());
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testErrorHandling() {
		$framework = $this->getFrameworkForException(new \RuntimeException());
		$response = $framework->handle(new Request());
		$this->assertEquals(500, $response->getStatusCode());
	}

	public function testControllerResponse() {
		$dispatcher = $this->createMock(EventDispatcher::class);
		$matcher = $this->createMock(UrlMatcherInterface::class);

		$matcher
				->expects($this->once())
				->method('match')
				->will(
						$this->returnValue(
								[
										'_route' => 'is_leap_year/{year}',
										'year' => '2000',
										'_controller' => [new LeapYearController(), 'index'],
								]));

		$matcher
				->expects($this->once())
				->method('getContext')
				->will($this->returnValue($this->createMock(RequestContext::class)));

		$controllerResolver = new ControllerResolver();
		$argumentResolver = new ArgumentResolver();

		$framework = new Framework($dispatcher, $matcher, $controllerResolver, $argumentResolver);

		$response = $framework->handle(new Request());

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertStringContainsString('Yep, this is a leap year!', $response->getContent());
	}

	public function getFrameWorkForException($exception) : Framework {
		$dispatcher = $this->createMock(EventDispatcher::class);
		$matcher = $this->createMock(UrlMatcherInterface::class);

		$matcher
				->expects($this->once())
				->method('match')
				->will($this->throwException($exception));

		$matcher
				->expects($this->once())
				->method('getContext')
				->will($this->returnValue($this->createMock(RequestContext::class)));

		$controllerResolver = $this->createMock(ControllerResolverInterface::class);
		$argumentResolver = $this->createMock(ArgumentResolverInterface::class);

		return new Framework($dispatcher, $matcher, $controllerResolver, $argumentResolver);
	}
}

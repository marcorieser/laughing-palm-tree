<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$container = include __DIR__.'/../src/container.php';

$request = Request::createFromGlobals();
$response = $container->get('framework')->handle($request);

$response->send();

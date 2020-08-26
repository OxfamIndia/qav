<?php
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';

$kernel = new DrupalKernel('prod', $autoloader);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);

// ID of the user.
$uid = 1;
$user = Drupal\user\Entity\User::load($uid);

user_login_finalize($user);

$response->send();
$kernel->terminate($request, $response);
?>
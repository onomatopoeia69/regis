<?php

use App\Controllers\HomeController;
use App\Controllers\LoginController;


/**
 * ================================================================
 * Login 
 * ---------------------------------------------------------------
 * File      : LoginController.php
 * Module    : Faculty
 * Protected by : Guest and Throttle
 * ================================================================
 */



$r->addRoute('GET', '/login', [
    'middleware' => ['guest','throttle'],
    'uses' => [LoginController::class, 'index']
]);


$r->post('/login',[LoginController::class, 'login']);


/**
 * ================================================================
 * Home or Dashboard
 * ---------------------------------------------------------------
 * File      : HomeController.php
 * Module    : Faculty
 * Protected by : Auth
 * ================================================================
 */

$r->addRoute('GET', '/home', [
    'middleware' => ['auth'],
    'uses' => [HomeController::class, 'index']
]);

$r->addRoute('GET', '/logout', [
    'middleware' => ['auth'],
    'uses' => [LoginController::class, 'logout']
]);







<?php

if(SESSION_STATUS() === PHP_SESSION_NONE){
    session_start();
}

if(empty($_SESSION['csrf_token'])){
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verifyCsrfToken($token){
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
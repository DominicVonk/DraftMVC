<?php
$_SERVER['HTTP_HOST'] = '';
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REQUEST_URI'] = implode(' ', array_slice($argv, 1));
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'DraftMVC CLI';
chdir(dirname(__DIR__, 4) . '/App');

require(getcwd() . '/boot.php');

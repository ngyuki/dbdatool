<?php
$host = getenv('MYSQL_HOST');
$port = getenv('MYSQL_PORT');
$dbname = getenv('MYSQL_DATABASE');
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');

return [
    'dsn' => "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
    'username' => $username,
    'password' => $password,
];

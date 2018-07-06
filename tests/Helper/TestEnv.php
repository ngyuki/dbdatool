<?php
namespace Test\Helper;

use PDO;

class TestEnv
{
    private static $pdo;

    public function pdo()
    {
        if (self::$pdo === null) {
            $host = getenv('MYSQL_HOST');
            $port = getenv('MYSQL_PORT');
            $dbname = getenv('MYSQL_DATABASE');
            $username = getenv('MYSQL_USER');
            $password = getenv('MYSQL_PASSWORD');

            $dsn = sprintf("mysql:dbname=$dbname;host=$host;port=$port;charset=utf8");

            self::$pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return self::$pdo;
    }

    public function clear()
    {
        $dbname = getenv('MYSQL_DATABASE');
        $this->pdo()->exec("drop database $dbname");
        $this->pdo()->exec("create database $dbname");
        $this->pdo()->exec("use $dbname");
        return $this;
    }

    public function load($file)
    {
        $content = file_get_contents($file);
        $sqls = explode(';', $content);
        foreach ($sqls as $sql) {
            $sql = trim($sql);
            if (strlen($sql)) {
                $this->pdo()->exec($sql);
            }
        }
        return $this;
    }
}

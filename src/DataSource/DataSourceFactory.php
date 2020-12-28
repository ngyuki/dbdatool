<?php
namespace ngyuki\DbdaTool\DataSource;

use PDO;

class DataSourceFactory
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(string $arg): DataSourceInterface
    {
        if ($arg === '@') {
            return self::createByConfig($this->config);
        }

        if ($arg === '!') {
            return new EmptySource();
        }

        if ($arg === '-') {
            return new PipeSource(STDIN);
        }

        if (preg_match('/^\w\w+:/', $arg)) {
            return self::createByDsn($arg);
        }

        if (preg_match('/\.php$/', $arg)) {
            /** @noinspection PhpIncludeInspection */
            return static::createByConfig(require $arg);
        }

        return new FileSource($arg);
    }

    public static function createByConfig(array $config)
    {
        $pdo = $config['pdo'] ?? null;
        if ($pdo) {
            return new ConnectionSource($pdo);
        }

        $dsn = $config['dsn'] ?? null;
        if ($dsn === null) {
            throw new \RuntimeException("Need specify 'dsn' or 'pdo' in config file.");
        }

        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;

        return new ConnectionSource(self::createPdo($dsn, $username, $password));
    }

    public static function createByDsn(string $dsn)
    {
        // @phan-suppress-next-line PhanSuspiciousBinaryAddLists
        list($driver, $param, $username, $password) = explode(':', $dsn, 4) + [null, null, null, null];
        return new ConnectionSource(self::createPdo("$driver:$param", $username, $password));
    }

    private static function createPdo($dsn, $username, $password)
    {
        try {
            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $ex) {
            // @phan-suppress-next-line PhanTypeMismatchArgumentInternal
            throw new \RuntimeException("Unable connect PDO using '$dsn'", $ex->getCode(), $ex);
        }
    }
}

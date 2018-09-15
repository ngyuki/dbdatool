<?php
namespace ngyuki\DbdaTool\DataSource;

use ngyuki\DbdaTool\Schema\Schema;
use ngyuki\DbdaTool\SchemaReverser\SchemaReverserFactory;
use PDO;

class ConnectionSource implements DataSourceInterface, ConnectionSourceInterface
{
    private $pdo;

    /**
     * {@inheritdoc}
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Schema
     */
    public function getSchema()
    {
        return (new SchemaReverserFactory($this->pdo))->create()->reverse();
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->pdo;
    }
}

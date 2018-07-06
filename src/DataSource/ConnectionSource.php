<?php
namespace ngyuki\DbdaTool\DataSource;

use ngyuki\DbdaTool\SchemaReverser\SchemaReverserFactory;
use ngyuki\DbdaTool\Table;
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
     * @return Table[]
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

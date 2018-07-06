<?php
namespace ngyuki\DbdaTool\SchemaReverser;

use PDO;

class SchemaReverserFactory
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(): SchemaReverserInterface
    {
        return new MySqlSchemaReverser($this->pdo);
    }
}

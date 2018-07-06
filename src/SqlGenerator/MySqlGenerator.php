<?php
namespace ngyuki\DbdaTool\SqlGenerator;

use ngyuki\DbdaTool\SchemaDiff;
use PDO;

class MySqlGenerator extends PseudoGenerator
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function quoteValue($val): string
    {
        return $this->pdo->quote($val);
    }

    public function diff(SchemaDiff $diff): array
    {
        return array_merge(
            ['SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0'],
            parent::diff($diff),
            ['SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS']
        );
    }
}

<?php
namespace ngyuki\DbdaTool\DataSource;

use PDO;

interface ConnectionSourceInterface
{
    /**
     * @return PDO
     */
    public function getConnection();
}

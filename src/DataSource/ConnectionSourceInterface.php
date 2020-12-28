<?php
namespace ngyuki\DbdaTool\DataSource;

use PDO;

interface ConnectionSourceInterface extends DataSourceInterface
{
    /**
     * @return PDO
     */
    public function getConnection();
}

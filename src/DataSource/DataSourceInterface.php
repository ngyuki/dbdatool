<?php
namespace ngyuki\DbdaTool\DataSource;

use ngyuki\DbdaTool\Schema\Schema;

interface DataSourceInterface
{
    /**
     * @return Schema
     */
    public function getSchema();
}

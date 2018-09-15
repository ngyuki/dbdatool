<?php
namespace ngyuki\DbdaTool\DataSource;

use ngyuki\DbdaTool\Schema\Table;

interface DataSourceInterface
{
    /**
     * @return Table[]
     */
    public function getSchema();
}

<?php
namespace ngyuki\DbdaTool\DataSource;

use ngyuki\DbdaTool\Table;

interface DataSourceInterface
{
    /**
     * @return Table[]
     */
    public function getSchema();
}

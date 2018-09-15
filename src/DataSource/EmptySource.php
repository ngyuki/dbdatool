<?php
namespace ngyuki\DbdaTool\DataSource;

use ngyuki\DbdaTool\Schema\Schema;

class EmptySource implements DataSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return new Schema();
    }
}

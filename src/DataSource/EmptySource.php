<?php
namespace ngyuki\DbdaTool\DataSource;

class EmptySource implements DataSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return [];
    }
}

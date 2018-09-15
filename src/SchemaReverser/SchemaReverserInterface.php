<?php
namespace ngyuki\DbdaTool\SchemaReverser;

use ngyuki\DbdaTool\Schema\Table;

interface SchemaReverserInterface
{
    /**
     * @return Table[]
     */
    public function reverse();
}

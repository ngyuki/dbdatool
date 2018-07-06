<?php
namespace ngyuki\DbdaTool\SchemaReverser;

use ngyuki\DbdaTool\Table;

interface SchemaReverserInterface
{
    /**
     * @return Table[]
     */
    public function reverse();
}

<?php
namespace ngyuki\DbdaTool\SchemaReverser;

use ngyuki\DbdaTool\Schema\Schema;

interface SchemaReverserInterface
{
    /**
     * @return Schema
     */
    public function reverse();
}

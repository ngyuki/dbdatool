<?php
namespace ngyuki\DbdaTool\Diff;

use ngyuki\DbdaTool\Schema\Table;

class SchemaDiff
{
    /**
     * @var Table[]
     */
    public $addTables = [];

    /**
     * @var Table[]
     */
    public $dropTables = [];

    /**
     * @var TableDiff[]
     */
    public $changeTables = [];
}

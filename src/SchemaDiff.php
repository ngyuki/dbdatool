<?php
namespace ngyuki\DbdaTool;

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

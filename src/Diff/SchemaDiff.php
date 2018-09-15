<?php
namespace ngyuki\DbdaTool\Diff;

use ngyuki\DbdaTool\Schema\Table;
use ngyuki\DbdaTool\Schema\View;

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

    /**
     * @var View[]
     */
    public $addViews = [];

    /**
     * @var View[]
     */
    public $dropViews = [];

    /**
     * @var View[]
     */
    public $changeViews = [];
}

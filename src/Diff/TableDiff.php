<?php
namespace ngyuki\DbdaTool\Diff;

use ngyuki\DbdaTool\Schema\CheckConstraint;
use ngyuki\DbdaTool\Schema\Column;
use ngyuki\DbdaTool\Schema\ForeignKey;
use ngyuki\DbdaTool\Schema\Index;
use ngyuki\DbdaTool\Schema\Table;

/**
 * @property string             $name
 * @property Table              $table
 * @property Column[]           $addColumns
 * @property Column[]           $dropColumns
 * @property Column[]           $changeColumns
 * @property Index[]            $addIndexes
 * @property Index[]            $dropIndexes
 * @property ForeignKey[]       $addForeignKeys
 * @property ForeignKey[]       $dropForeignKeys
 * @property CheckConstraint[]  $addCheckConstraints
 * @property CheckConstraint[]  $dropCheckConstraints
 * @property string[]           $changeOptions
 */
class TableDiff extends \ArrayObject implements \JsonSerializable
{
    public function __construct($input = array())
    {
        $input = array_merge([
            'name' => '',
            'table' => null,
            'addColumns' => [],
            'dropColumns' => [],
            'changeColumns' => [],
            'addIndexes' => [],
            'dropIndexes' => [],
            'addForeignKeys' => [],
            'dropForeignKeys' => [],
            'addCheckConstraints' => [],
            'dropCheckConstraints' => [],
            'changeOptions' => [],
        ], $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_diff_key($this->getArrayCopy(), ['name' => '']);
    }
}

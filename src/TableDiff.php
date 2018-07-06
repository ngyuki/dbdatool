<?php
namespace ngyuki\DbdaTool;

/**
 * @property string       $name
 * @property Table        $table
 * @property Column[]     $addColumns
 * @property Column[]     $dropColumns
 * @property Column[]     $changeColumns
 * @property Index[]      $addIndexes
 * @property Index[]      $dropIndexes
 * @property ForeignKey[] $addForeignKeys
 * @property ForeignKey[] $dropForeignKeys
 * @property string[]     $changeOptions
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
            'changeOptions' => [],
        ], $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    public function jsonSerialize()
    {
        return array_diff_key($this->getArrayCopy(), ['name' => '']);
    }
}

<?php
namespace ngyuki\DbdaTool;

/**
 * @property string       $name
 * @property Column[]     $columns
 * @property Index[]      $indexes
 * @property ForeignKey[] $foreignKeys
 * @property string[]     $options
 */
class Table extends \ArrayObject implements \JsonSerializable
{
    public function __construct($input = array())
    {
        $input = array_merge([
            'name' => '',
            'columns' => [],
            'indexes' => [],
            'foreignKeys' => [],
            'options' => [],
        ], $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    public function jsonSerialize()
    {
        return array_diff_key($this->getArrayCopy(), ['name' => '']);
    }
}

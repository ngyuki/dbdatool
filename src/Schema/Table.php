<?php
namespace ngyuki\DbdaTool\Schema;

/**
 * @property string             $name
 * @property Column[]           $columns
 * @property Index[]            $indexes
 * @property ForeignKey[]       $foreignKeys
 * @property CheckConstraint[]  $checkConstraints
 * @property string[]           $options
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
            'checkConstraints' => [],
            'options' => [],
        ], $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_diff_key($this->getArrayCopy(), ['name' => '']);
    }
}

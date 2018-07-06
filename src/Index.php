<?php
namespace ngyuki\DbdaTool;

/**
 * @property $name string
 * @property $type string
 * @property $columns string[]
 * @property $comment string
 */
class Index extends \ArrayObject implements \JsonSerializable
{
    public function __construct($input = array())
    {
        $input = array_merge([
            'name' => '',
            'type' => '',
            'columns' => [],
            'comment' => '',
        ], $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    public function jsonSerialize()
    {
        return array_diff_key($this->getArrayCopy(), ['name' => '']);
    }

    public function isPrimary()
    {
        return strtoupper($this->type) === 'PRIMARY';
    }

    public function isUnique()
    {
        return strtoupper($this->type) === 'UNIQUE';
    }
}

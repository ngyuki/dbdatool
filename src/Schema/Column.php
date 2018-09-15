<?php
namespace ngyuki\DbdaTool\Schema;

/**
 * @property $name string
 * @property $default mixed
 * @property $nullable bool
 * @property $type string
 * @property $charset string
 * @property $collation string
 * @property $autoIncrement bool
 * @property $comment string
 * @property $generated string
 * @property $expression string
 */
class Column extends \ArrayObject implements \JsonSerializable
{
    public function __construct($input = array())
    {
        $input = array_merge([
            'name' => '',
            'default' => null,
            'nullable' => true,
            'type' => '',
            'charset' => '',
            'collation' => '',
            'autoIncrement' => false,
            'comment' => '',
            'generated' => '',
            'expression' => '',
        ], $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    public function jsonSerialize()
    {
        return array_diff_key($this->getArrayCopy(), ['name' => '']);
    }
}

<?php
namespace ngyuki\DbdaTool\Schema;

/**
 * @property $name string
 * @property $columns string[]
 * @property $refTable string
 * @property $refColumns string[]
 * @property $onUpdate string
 * @property $onDelete string
 */
class ForeignKey extends \ArrayObject implements \JsonSerializable
{
    public function __construct($input = array())
    {
        $input = array_merge([
            'name' => '',
            'columns' => [],
            'refTable' => '',
            'refColumns' => [],
            'onUpdate' => '',
            'onDelete' => '',
        ], $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_diff_key($this->getArrayCopy(), ['name' => '']);
    }
}

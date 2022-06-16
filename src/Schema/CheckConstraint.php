<?php
namespace ngyuki\DbdaTool\Schema;

/**
 * @property string $name
 * @property string $expr
 * @property bool   $enforced
 */
class CheckConstraint extends \ArrayObject implements \JsonSerializable
{
    public function __construct($input = array())
    {
        $input = array_merge([
            'name' => '',
            'expr' => '',
            'enforced' => false,
        ], $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_diff_key($this->getArrayCopy(), ['name' => '']);
    }
}

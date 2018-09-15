<?php
namespace ngyuki\DbdaTool\Schema;

/**
 * @property string $name
 * @property string $definition
 */
class View extends \ArrayObject implements \JsonSerializable
{
    public function __construct($input = array())
    {
        $input = array_merge([
            'name' => '',
            'definition' => '',
        ], $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    public function jsonSerialize()
    {
        return array_diff_key($this->getArrayCopy(), ['name' => '']);
    }
}

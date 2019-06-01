<?php
namespace ngyuki\DbdaTool\Schema;

/**
 * @property string $name
 * @property mixed  $default
 * @property bool   $defaultGenerated
 * @property bool   $nullable
 * @property string $type
 * @property string $charset
 * @property string $collation
 * @property bool   $autoIncrement
 * @property bool   $onUpdateCurrentTimestamp
 * @property string $comment
 * @property string $generated
 * @property string $expression
 */
class Column extends \ArrayObject implements \JsonSerializable
{
    private static $defaults = [
        'name' => '',
        'default' => null,
        'defaultGenerated' => false,
        'nullable' => true,
        'type' => '',
        'charset' => '',
        'collation' => '',
        'autoIncrement' => false,
        'onUpdateCurrentTimestamp' => false,
        'comment' => '',
        'generated' => '',
        'expression' => '',
    ];

    public function __construct($input = array())
    {
        $input = array_merge(static::$defaults, $input);

        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    public function jsonSerialize()
    {
        $optionals = [
            'defaultGenerated',
            'charset',
            'collation',
            'autoIncrement',
            'onUpdateCurrentTimestamp',
            'comment',
            'generated',
            'expression',
        ];
        $arr = array_diff_key($this->getArrayCopy(), ['name' => '']);
        foreach ($optionals as $key) {
            if (array_key_exists($key, $arr)) {
                if ($arr[$key] === static::$defaults[$key]) {
                    unset($arr[$key]);
                }
            }
        }
        return $arr;
    }
}

<?php
namespace ngyuki\DbdaTool\Schema;

class Schema implements \JsonSerializable
{
    /**
     * @var Table[]
     */
    public $tables = [];

    /**
     * @var View[]
     */
    public $views = [];

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return (array)$this;
    }
}

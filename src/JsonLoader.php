<?php
namespace ngyuki\DbdaTool;

use ngyuki\DbdaTool\Schema\Column;
use ngyuki\DbdaTool\Schema\ForeignKey;
use ngyuki\DbdaTool\Schema\Index;
use ngyuki\DbdaTool\Schema\Table;

class JsonLoader
{
    /**
     * @param $filename
     * @return Table[]
     */
    public function load($filename)
    {
        $content = file_get_contents($filename);
        $arr = json_decode($content, true);
        return $this->fromArray($arr);
    }

    /**
     * @param array $array
     * @return Table[]
     */
    private function fromArray(array $array)
    {
        /** @var $tables Table[] */
        $tables = [];

        foreach ($array as $name => $arr) {
            $table = new Table($arr);
            $table->name = $name;

            array_walk($table->columns, function (array &$column, string $name) {
                $column = new Column($column);
                $column->name = $name;
            });

            array_walk($table->indexes, function (array &$index, string $name) {
                $index = new Index($index);
                $index->name = $name;
            });

            array_walk($table->foreignKeys, function (array &$foreignKey, string $name) {
                $foreignKey = new ForeignKey($foreignKey);
                $foreignKey->name = $name;
            });

            $tables[$table->name] = $table;
        }

        return $tables;
    }
}

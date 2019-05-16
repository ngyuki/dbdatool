<?php
namespace ngyuki\DbdaTool;

use ngyuki\DbdaTool\Schema\Column;
use ngyuki\DbdaTool\Schema\ForeignKey;
use ngyuki\DbdaTool\Schema\Index;
use ngyuki\DbdaTool\Schema\Schema;
use ngyuki\DbdaTool\Schema\Table;
use ngyuki\DbdaTool\Schema\View;

class JsonLoader
{
    /**
     * @param $filename
     * @return Schema
     */
    public function load($filename)
    {
        $content = file_get_contents($filename);
        return $this->parse($content);
    }

    /**
     * @param string $content
     * @return Schema
     */
    public function parse($content)
    {
        $arr = json_decode($content, true);
        return $this->fromArray($arr);
    }

    /**
     * @param array $array
     * @return Schema
     */
    private function fromArray(array $array)
    {
        $schema = new Schema();

        if (!array_key_exists('tables', $array) && !array_key_exists('views', $array)) {
            // fallback v0.0.2
            $array['tables'] = $array;
        }

        $tables = $array['tables'] ?? [];
        foreach ($tables as $name => $arr) {
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

            $schema->tables[$table->name] = $table;
        }

        $views = $array['views'] ?? [];
        foreach ($views as $name => $arr) {
            $view = new View($arr);
            $view->name = $name;
            $schema->views[$view->name] = $view;
        }

        return $schema;
    }
}

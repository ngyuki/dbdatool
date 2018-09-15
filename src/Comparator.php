<?php
namespace ngyuki\DbdaTool;

use ngyuki\DbdaTool\Diff\SchemaDiff;
use ngyuki\DbdaTool\Diff\TableDiff;
use ngyuki\DbdaTool\Schema\Table;

class Comparator
{
    /**
     * @param Table[] $tables1
     * @param Table[] $tables2
     * @return SchemaDiff
     */
    public function compare(array $tables1, array $tables2): SchemaDiff
    {
        $tables1 = array_reduce($tables1, function ($r, Table $t) {
            $r[$t->name] = $t;
            return $r;
        }, []);
        $tables2 = array_reduce($tables2, function ($r, Table $t) {
            $r[$t->name] = $t;
            return $r;
        }, []);

        $diff = new SchemaDiff();

        foreach ($tables2 as $name => $table2) {
            if (!array_key_exists($name, $tables1)) {
                $diff->addTables[$name] = $table2;
                continue;
            }
            $tableDiff = $this->diffTable($tables1[$name], $table2);
            if ($tableDiff) {
                $diff->changeTables[$name] = $tableDiff;
            }
        }

        foreach ($tables1 as $name => $table1) {
            if (!array_key_exists($name, $tables2)) {
                $diff->dropTables[$name] = $table1;
            }
        }

        return $diff;
    }

    /**
     * @param Table $table1
     * @param Table $table2
     * @return TableDiff|null
     */
    public function diffTable(Table $table1, Table $table2)
    {
        $changes = 0;
        $tableDiff = new TableDiff();
        $tableDiff->name = $table2->name;
        $tableDiff->table = $table2;

        $afters1 = [];
        foreach ($table1->columns as $name => $_) {
            if (array_key_exists($name, $table2->columns)) {
                end($afters1);
                $afters1[$name] = key($afters1);
            }
        }

        $afters2 = [];
        foreach ($table2->columns as $name => $_) {
            if (array_key_exists($name, $table1->columns)) {
                end($afters2);
                $afters2[$name] = key($afters2);
            }
        }

        foreach ($table2->columns as $name => $column2) {
            if (!array_key_exists($name, $table1->columns)) {
                $tableDiff->addColumns[$name] = $column2;
                $changes++;
                continue;
            }
        }

        foreach ($table1->columns as $name => $column1) {
            if (!array_key_exists($name, $table2->columns)) {
                $tableDiff->dropColumns[$name] = $column1;
                $changes++;
            }
        }

        foreach ($table2->columns as $name => $column2) {
            if (array_key_exists($name, $table1->columns)) {
                $column1 = $table1->columns[$name];
                if ($afters1[$name] != $afters2[$name]) {
                    $afters1 = $this->moveLinkedList($afters1, $name, $afters2[$name]);
                    $tableDiff->changeColumns[$name] = $column2;
                    $changes++;
                    continue;
                }
                if ((array)$column1 != (array)$column2) {
                    $tableDiff->changeColumns[$name] = $column2;
                    $changes++;
                    continue;
                }
            }
        }

        foreach ($table2->indexes as $name => $index2) {
            if (!array_key_exists($name, $table1->indexes)) {
                $tableDiff->addIndexes[$name] = $index2;
                $changes++;
                continue;
            }
            $index1 = $table1->indexes[$name];
            if ((array)$index1 != (array)$index2) {
                $tableDiff->dropIndexes[$name] = $index1;
                $tableDiff->addIndexes[$name] = $index2;
                $changes++;
            }
        }

        foreach ($table1->indexes as $name => $index1) {
            if (!array_key_exists($name, $table2->indexes)) {
                $tableDiff->dropIndexes[$name] = $index1;
                $changes++;
            }
        }

        foreach ($table2->foreignKeys as $name => $foreignKey2) {
            if (!array_key_exists($name, $table1->foreignKeys)) {
                $tableDiff->addForeignKeys[$name] = $foreignKey2;
                $changes++;
                continue;
            }
            $foreignKey1 = $table1->foreignKeys[$name];
            if ((array)$foreignKey1 != (array)$foreignKey2) {
                $tableDiff->dropForeignKeys[$name] = $foreignKey1;
                $tableDiff->addForeignKeys[$name] = $foreignKey2;
                $changes++;
            }
        }

        foreach ($table1->foreignKeys as $name => $foreignKey1) {
            if (!array_key_exists($name, $table2->foreignKeys)) {
                $tableDiff->dropForeignKeys[$name] = $foreignKey1;
                $changes++;
            }
        }

        if ((array)$table1->options != (array)$table2->options) {
            $tableDiff->changeOptions = $table2->options;
            $changes++;
        }

        return $changes ? $tableDiff : null;
    }

    private function moveLinkedList(array $list, $name, $newAfter): array
    {
        foreach ($list as $before => $after) {
            if ($after === $name) {
                $list[$before] = $list[$name];
            }
            if ($after === $newAfter) {
                $list[$before] = $name;
            }
        }
        $list[$name] = $newAfter;
        return $list;
    }
}

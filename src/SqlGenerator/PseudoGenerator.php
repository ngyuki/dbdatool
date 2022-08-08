<?php
namespace ngyuki\DbdaTool\SqlGenerator;

use ngyuki\DbdaTool\Diff\SchemaDiff;
use ngyuki\DbdaTool\Schema\CheckConstraint;
use ngyuki\DbdaTool\Schema\Column;
use ngyuki\DbdaTool\Schema\ForeignKey;
use ngyuki\DbdaTool\Schema\Index;
use ngyuki\DbdaTool\Schema\Table;

class PseudoGenerator
{
    protected function quoteValue($val): string
    {
        return "'" . addslashes($val) . "'";
    }

    protected function quote(string $name): string
    {
        return "`$name`";
    }

    protected function quoteJoin(array $pieces): string
    {
        $arr = [];
        foreach ($pieces as $piece) {
            $arr[] = $this->quote($piece);
        }
        return implode(', ', $arr);
    }

    public function diff(SchemaDiff $diff): array
    {
        $sql = [];

        foreach ($diff->changeTables as $tableDiff) {
            foreach ($tableDiff->dropCheckConstraints as $checkConstraint) {
                $sql[] = "ALTER TABLE {$this->quote($tableDiff->name)} DROP CHECK {$this->quote($checkConstraint->name)}";
            }
        }

        foreach ($diff->changeTables as $tableDiff) {
            foreach ($tableDiff->dropForeignKeys as $foreignKey) {
                $sql[] = "ALTER TABLE {$this->quote($tableDiff->name)} DROP FOREIGN KEY {$this->quote($foreignKey->name)}";
            }
        }

        foreach ($diff->dropViews as $view) {
            $sql[] = "DROP VIEW {$this->quote($view->name)}";
        }

        foreach ($diff->dropTables as $table) {
            $sql[] = "DROP TABLE {$this->quote($table->name)}";
        }

        foreach ($diff->changeTables as $tableDiff) {
            $alters = [];

            foreach ($tableDiff->dropIndexes as $index) {
                if ($index->isPrimary()) {
                    $alters[] = "DROP PRIMARY KEY";
                } else {
                    $alters[] = "DROP INDEX {$this->quote($index->name)}";
                }
            }

            foreach ($tableDiff->dropColumns as $column) {
                $alters[] = "DROP COLUMN {$this->quote($column->name)}";
            }

            foreach ($tableDiff->addColumns as $column) {
                $alters[] = "ADD COLUMN {$this->columnDefinitionWithAfter($tableDiff->table, $column)}";
            }

            foreach ($tableDiff->changeColumns as $column) {
                $alters[] = "CHANGE COLUMN {$this->quote($column->name)} {$this->columnDefinitionWithAfter($tableDiff->table, $column)}";
            }

            foreach ($tableDiff->addIndexes as $index) {
                $alters[] = "ADD {$this->indexDefinition($index)}";
            }

            if ($tableDiff->changeOptions) {
                $alters[] = $this->tableOptions($tableDiff->changeOptions);
            }

            if ($alters) {
                $alters = "\n  " . implode(",\n  ", $alters);
                $sql[] = "ALTER TABLE {$this->quote($tableDiff->name)}{$alters}";
            }
        }

        foreach ($diff->addTables as $table) {
            $sql[] = $this->createTable($table);
        }

        foreach ($diff->addTables as $table) {
            foreach ($table->foreignKeys as $foreignKey) {
                $sql[] = "ALTER TABLE {$this->quote($table->name)} {$this->addForeignKey($foreignKey)}";
            }
            foreach ($table->checkConstraints as $checkConstraint) {
                $sql[] = "ALTER TABLE {$this->quote($table->name)} {$this->addCheckConstraint($checkConstraint)}";
            }
        }

        foreach ($diff->changeTables as $tableDiff) {
            foreach ($tableDiff->addForeignKeys as $foreignKey) {
                $sql[] = "ALTER TABLE {$this->quote($tableDiff->name)} {$this->addForeignKey($foreignKey)}";
            }
            foreach ($tableDiff->addCheckConstraints as $checkConstraint) {
                $sql[] = "ALTER TABLE {$this->quote($tableDiff->name)} {$this->addCheckConstraint($checkConstraint)}";
            }
        }

        foreach ($diff->changeViews as $view) {
            $sql[] = "CREATE OR REPLACE VIEW {$this->quote($view->name)} AS {$view->definition}";
        }

        foreach ($diff->addViews as $view) {
            $sql[] = "CREATE OR REPLACE VIEW {$this->quote($view->name)} AS {$view->definition}";
        }

        return $sql;
    }

    public function createTable(Table $table): string
    {
        $columnsSql = [];
        foreach ($table->columns as $column) {
            $columnsSql[] = $this->columnDefinition($column);
        }

        foreach ($table->indexes as $index) {
            $columnsSql[] = $this->indexDefinition($index);
        }

        return sprintf(
            /** @lang text */
            "CREATE TABLE %s (%s) %s",
            $this->quote($table->name),
            "\n  " . implode(",\n  ", $columnsSql) . "\n",
            $this->tableOptions($table->options)
        );
    }

    public function columnDefinition(Column $column): string
    {
        $parts = [];
        $parts[] = $this->quote($column->name);
        $parts[] = $column->type;

        if (strlen($column->charset)) {
            $parts[] = "CHARACTER SET {$column->charset}";
        }

        if (strlen($column->collation)) {
            $parts[] = "COLLATE {$column->collation}";
        }

        if (strlen($column->generated)) {
            $parts[] = "GENERATED ALWAYS AS ($column->expression) $column->generated";
        }

        if ($column->nullable) {
            $parts[] = 'NULL';
        } else {
            $parts[] = 'NOT NULL';
        }

        if (strlen($column->generated)) {
            // noop
        } elseif ($column->default !== null) {
            if ($column->defaultGenerated) {
                $parts[] = "DEFAULT {$column->default}";
            } else {
                $parts[] = "DEFAULT {$this->quoteValue($column->default)}";
            }
        } elseif ($column->nullable) {
            $parts[] = "DEFAULT NULL";
        }

        if ($column->autoIncrement) {
            $parts[] = "auto_increment";
        }

        if ($column->onUpdateCurrentTimestamp) {
            $parts[] = "ON UPDATE CURRENT_TIMESTAMP";
        }

        if (strlen($column->comment)) {
            $parts[] = "COMMENT {$this->quoteValue($column->comment)}";
        }

        return implode(' ', $parts);
    }

    public function columnDefinitionWithAfter(Table $table, Column $column): string
    {
        $sql = $this->columnDefinition($column);

        $prev = null;
        foreach ($table->columns as $curr) {
            if ($curr->name === $column->name) {
                break;
            }
            $prev = $curr;
        }

        if ($prev === null) {
            $sql .= ' FIRST';
        } else {
            $sql .= " AFTER {$this->quote($prev->name)}";
        }

        return $sql;
    }

    public function indexDefinition(Index $index): string
    {
        if (strlen($index->comment)) {
            $comment = " COMMENT {$this->quoteValue($index->comment)}";
        } else {
            $comment = "";
        }

        if ($index->isPrimary()) {
            return "PRIMARY KEY ({$this->quoteJoin($index->columns)}){$comment}";
        } elseif ($index->isUnique()) {
            return "UNIQUE INDEX {$this->quote($index->name)} ({$this->quoteJoin($index->columns)}){$comment}";
        } else {
            return "INDEX {$this->quote($index->name)} ({$this->quoteJoin($index->columns)}){$comment}";
        }
    }

    public function addForeignKey(ForeignKey $foreignKey): string
    {
        return sprintf(
            /** @lang text */
            "ADD CONSTRAINT %s FOREIGN KEY (%s) references %s (%s) on update %s on delete %s",
            $this->quote($foreignKey->name),
            $this->quoteJoin($foreignKey->columns),
            $this->quote($foreignKey->refTable),
            $this->quoteJoin($foreignKey->refColumns),
            $foreignKey->onUpdate,
            $foreignKey->onDelete
        );
    }

    public function addCheckConstraint(CheckConstraint $checkConstraint): string
    {
        if ($checkConstraint->enforced) {
            return "ADD CONSTRAINT {$this->quote($checkConstraint->name)} CHECK {$checkConstraint->expr}";
        } else {
            return "ADD CONSTRAINT {$this->quote($checkConstraint->name)} CHECK {$checkConstraint->expr} NOT ENFORCED";
        }
    }

    public function tableOptions($options)
    {
        $mapOptions = [
            'engine' => 'ENGINE',
            'charset' => 'CHARSET',
            'collation' => 'COLLATE',
            'comment' => 'COMMENT',
            'row_format' => 'ROW_FORMAT',
        ];

        $optionsSql = [];
        foreach ($mapOptions as $key => $name) {
            if (array_key_exists($key, $options)) {
                $opt = $options[$key];
                if ($name === 'COMMENT') {
                    $opt = $this->quoteValue($opt);
                }
                if (strlen($opt)) {
                    $optionsSql[] = "$name=$opt";
                }
            }
        }

        return implode(' ', $optionsSql);
    }
}

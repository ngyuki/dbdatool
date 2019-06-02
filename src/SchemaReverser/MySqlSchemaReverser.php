<?php
namespace ngyuki\DbdaTool\SchemaReverser;

use ngyuki\DbdaTool\Schema\CheckConstraint;
use ngyuki\DbdaTool\Schema\Column;
use ngyuki\DbdaTool\Schema\ForeignKey;
use ngyuki\DbdaTool\Schema\Index;
use ngyuki\DbdaTool\Schema\Schema;
use ngyuki\DbdaTool\Schema\Table;
use ngyuki\DbdaTool\Schema\View;
use PDO;

class MySqlSchemaReverser implements SchemaReverserInterface
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Schema
     */
    public function reverse()
    {
        $schema = new Schema();
        $schema->tables = $this->reverseTables();
        $schema->views = $this->reverseViews();
        return $schema;
    }

    /**
     * @return Table[]
     */
    private function reverseTables()
    {
        /** @var $tables Table[] */
        $tables = [];

        $sql = "
            select TABLE_NAME, ENGINE, ROW_FORMAT, CHARACTER_SET_NAME, TABLE_COLLATION, TABLE_COMMENT
            from information_schema.TABLES
            left join information_schema.COLLATION_CHARACTER_SET_APPLICABILITY on COLLATION_NAME = TABLE_COLLATION
            where TABLE_SCHEMA = database() and TABLE_TYPE = 'BASE TABLE'
        ";

        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $table = new Table();
            $table->name = $row['TABLE_NAME'];
            $table->options = [
                'engine' => $row['ENGINE'],
                'row_format' => $row['ROW_FORMAT'],
                'charset' => $row['CHARACTER_SET_NAME'],
                'collation' => $row['TABLE_COLLATION'],
                'comment' => $row['TABLE_COMMENT'],
            ];
            $tables[$table->name] = $table;
        }

        $sql = "
            select *
            from information_schema.COLUMNS where TABLE_SCHEMA = database()
            order by TABLE_SCHEMA, TABLE_NAME, ORDINAL_POSITION
        ";

        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (!isset($tables[$row['TABLE_NAME']])) {
                continue;
            }
            $table = $tables[$row['TABLE_NAME']];

            $column = new Column();
            $column->name = (string)$row['COLUMN_NAME'];
            $column->default = $row['COLUMN_DEFAULT'];
            $column->nullable = $row['IS_NULLABLE'] === 'YES';
            $column->charset = (string)$row['CHARACTER_SET_NAME'];
            $column->collation = (string)$row['COLLATION_NAME'];
            $column->type = (string)$row['COLUMN_TYPE'];
            $column->comment = (string)$row['COLUMN_COMMENT'];

            if (preg_match('/auto_increment/', $row['EXTRA'])) {
                $column->autoIncrement = true;
            }

            if (preg_match('/on update CURRENT_TIMESTAMP/i', $row['EXTRA'])) {
                $column->onUpdateCurrentTimestamp = true;
            }

            if (preg_match('/STORED GENERATED/', $row['EXTRA'])) {
                $column->generated = 'STORED';
            }

            if (preg_match('/VIRTUAL GENERATED/', $row['EXTRA'])) {
                $column->generated = 'VIRTUAL';
            }

            if (preg_match('/DEFAULT_GENERATED/', $row['EXTRA'])) {
                $column->defaultGenerated = true;
            }

            $column->expression = (string)($row['GENERATION_EXPRESSION'] ?? null);

            $table->columns[$column->name] = $column;
        }

        $sql = "
            select TABLE_NAME, NON_UNIQUE, INDEX_NAME, COLUMN_NAME, NULLABLE, INDEX_COMMENT
            from information_schema.statistics where TABLE_SCHEMA = database()
            order by TABLE_SCHEMA, TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
        ";

        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (!isset($tables[$row['TABLE_NAME']])) {
                continue;
            }
            $table = $tables[$row['TABLE_NAME']];

            if (!isset($table->indexes[$row['INDEX_NAME']])) {
                $index = new Index();
                $index->name = $row['INDEX_NAME'];
            } else {
                $index = $table->indexes[$row['INDEX_NAME']];
            }

            $index->columns[] = $row['COLUMN_NAME'];
            $index->comment = $row['INDEX_COMMENT'];

            if ($row['INDEX_NAME'] === 'PRIMARY') {
                $index->type = 'PRIMARY';
            } elseif ($row['NON_UNIQUE'] == 0) {
                $index->type = 'UNIQUE';
            } else {
                $index->type = 'INDEX';
            }

            $table->indexes[$index->name] = $index;
        }

        $sql = "
            select CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME,
              REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, UPDATE_RULE, DELETE_RULE
            from information_schema.REFERENTIAL_CONSTRAINTS
            inner join information_schema.KEY_COLUMN_USAGE
              using (CONSTRAINT_SCHEMA, TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME)
            where CONSTRAINT_SCHEMA = database()
            order by CONSTRAINT_SCHEMA, TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION;
        ";

        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (!isset($tables[$row['TABLE_NAME']])) {
                continue;
            }
            $table = $tables[$row['TABLE_NAME']];

            if (!isset($table->foreignKeys[$row['CONSTRAINT_NAME']])) {
                $foreignKey = new ForeignKey();
                $foreignKey->name = $row['CONSTRAINT_NAME'];
            } else {
                $foreignKey = $table->foreignKeys[$row['CONSTRAINT_NAME']];
            }

            $foreignKey->columns[] = $row['COLUMN_NAME'];
            $foreignKey->refTable = $row['REFERENCED_TABLE_NAME'];
            $foreignKey->refColumns[] = $row['REFERENCED_COLUMN_NAME'];
            $foreignKey->onUpdate = $row['UPDATE_RULE'];
            $foreignKey->onDelete = $row['DELETE_RULE'];

            $table->foreignKeys[$foreignKey->name] = $foreignKey;
        }

        $sql = "
            select 1 from information_schema.TABLES
            where TABLE_SCHEMA = 'information_schema' AND TABLE_NAME = 'CHECK_CONSTRAINTS'
        ";

        $row = $this->pdo->query($sql)->fetch();
        if ($row) {
            $sql = '
                select TABLE_NAME, CONSTRAINT_NAME, CHECK_CLAUSE, ENFORCED
                from information_schema.CHECK_CONSTRAINTS
                inner join information_schema.TABLE_CONSTRAINTS
                    using (CONSTRAINT_CATALOG, CONSTRAINT_SCHEMA, CONSTRAINT_NAME)
                where CONSTRAINT_SCHEMA = database()
                order by TABLE_NAME, CONSTRAINT_NAME
            ';

            $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                if (!isset($tables[$row['TABLE_NAME']])) {
                    continue;
                }
                $table = $tables[$row['TABLE_NAME']];

                $checkConstraint = new CheckConstraint();
                $checkConstraint->name = $row['CONSTRAINT_NAME'];
                $checkConstraint->expr = $row['CHECK_CLAUSE'];
                $checkConstraint->enforced = $row['ENFORCED'] === 'YES';

                $table->checkConstraints[$checkConstraint->name] = $checkConstraint;
            }
        }

        return $tables;
    }

    /**
     * @return View[]
     */
    private function reverseViews()
    {
        /** @var $views View[] */
        $views = [];

        $sql = "select TABLE_NAME, VIEW_DEFINITION from information_schema.VIEWS where TABLE_SCHEMA = database()";

        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $view = new View();
            $view->name = $row['TABLE_NAME'];
            $view->definition = $row['VIEW_DEFINITION'];
            $views[$view->name] = $view;
        }

        return $views;
    }
}

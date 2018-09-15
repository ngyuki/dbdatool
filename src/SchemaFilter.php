<?php
namespace ngyuki\DbdaTool;

use ngyuki\DbdaTool\Schema\Schema;
use ngyuki\DbdaTool\Schema\Table;

class SchemaFilter
{
    /**
     * @var string[]
     */
    private $ignoreTablePatterns;

    public function setIgnoreTablePattern(array $ignoreTablePatterns = [])
    {
        $this->ignoreTablePatterns = $ignoreTablePatterns;
        return $this;
    }

    /**
     * @param Schema $schema
     * @return Schema
     */
    public function filter(Schema $schema)
    {
        foreach ($this->ignoreTablePatterns as $pattern) {
            $pattern = str_replace('/', '\\/', $pattern);
            $schema->tables = array_filter($schema->tables, function (Table $table) use ($pattern) {
                return preg_match("/$pattern/i", $table->name) === 0;
            });
        }
        return $schema;
    }
}

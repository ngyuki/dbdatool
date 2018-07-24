<?php
namespace ngyuki\DbdaTool;

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
     * @param Table[] $schema
     * @return Table[]
     */
    public function filter(array $schema)
    {
        foreach ($this->ignoreTablePatterns as $pattern) {
            $pattern = str_replace('/', '\\/', $pattern);
            $schema = array_filter($schema, function (Table $table) use ($pattern) {
                return preg_match("/$pattern/i", $table->name) === 0;
            });
        }
        return $schema;
    }
}

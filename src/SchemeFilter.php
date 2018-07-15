<?php
namespace ngyuki\DbdaTool;

class SchemeFilter
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
     * @param Table[] $scheme
     * @return Table[]
     */
    public function filter(array $scheme)
    {
        foreach ($this->ignoreTablePatterns as $pattern) {
            $pattern = str_replace('/', '\\/', $pattern);
            $scheme = array_filter($scheme, function (Table $table) use ($pattern) {
                return preg_match("/$pattern/i", $table->name) === 0;
            });
        }
        return $scheme;
    }
}

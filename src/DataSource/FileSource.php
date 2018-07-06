<?php
namespace ngyuki\DbdaTool\DataSource;

use ngyuki\DbdaTool\JsonLoader;

class FileSource implements DataSourceInterface
{
    /**
     * @var string
     */
    private $filename;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return (new JsonLoader())->load($this->filename);
    }
}

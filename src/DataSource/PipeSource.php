<?php
namespace ngyuki\DbdaTool\DataSource;

use ngyuki\DbdaTool\JsonLoader;

class PipeSource implements DataSourceInterface
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * {@inheritdoc}
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return (new JsonLoader())->parse(stream_get_contents($this->stream));
    }
}

<?php
namespace App\ElasticSearch;

use Elastica\Client;
use Symfony\Component\Yaml\Yaml;

class IndexBuilder
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function create()
    {
        $index = $this->client->getIndex('forumphp');

        $settings = Yaml::parse(
            file_get_contents(
                __DIR__.'/../../config/elasticsearch.yml'
            )
        );

        // We build our index settings and mapping
        $index->create($settings, true);

        return $index;
    }
}

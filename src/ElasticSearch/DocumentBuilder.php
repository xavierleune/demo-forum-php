<?php

namespace App\ElasticSearch;

use App\Entity\Link;
use Elastica\Document;

class DocumentBuilder
{
    public function getDocumentFromLink(Link $link)
    {
        return new Document(
            sha1($link->getLink()) . time(),
            $link->toArray(),
            "links" // Types are deprecated, to be removed in Elastic 7
        );
    }
}

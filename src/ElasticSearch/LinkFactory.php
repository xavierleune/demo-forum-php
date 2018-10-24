<?php

namespace App\ElasticSearch;

use App\Entity\Link;
use Elastica\Result;

class LinkFactory
{
    public function getLink(Result $result)
    {
        $data = $result->getSource();

        $link = new Link();
        $link
            ->setLink($data['link'])
            ->setImage($data['image'])
            ->setDescription($data['description'])
            ->setTitle($data['title'])
        ;

        return $link;
    }
}

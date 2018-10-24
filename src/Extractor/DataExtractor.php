<?php

namespace App\Extractor;

use App\Entity\Link;

class DataExtractor
{
    public function extractLinkFromUrl($url, $step = 1)
    {
        $steps = [
            1 => UrlCrawler1::class,
            UrlCrawler2::class,
            UrlCrawler3::class,
            UrlCrawler4::class,
            UrlCrawler5::class,
            UrlCrawler6::class,
        ];
        $urlCrawler = new $steps[$step]();

        $html = $urlCrawler->crawlUrl($url);

        if ($html === false) {
            return false;
        }

        $parser = new HtmlParser($html);

        $link = new Link();

        if (
            $parser->getTitle() === null ||
            $parser->getMeta('description') === null ||
            $parser->getMeta('image') === null
        ) {
            return false;
        }

        $link
            ->setTitle($parser->getTitle())
            ->setDescription($parser->getMeta('description'))
            ->setImage($parser->getMeta('image'))
            ->setLink($url)
        ;

        return $link;
    }
}

<?php

namespace App\Extractor;

class UrlCrawler2
{
    public function crawlUrl($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $url = str_replace($host, idn_to_ascii($host), $url);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'] )) {
            throw new \UnexpectedValueException('Wrong URL');
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $output = curl_exec($curl);
        if (curl_errno($curl) > 0) {
            throw new \RuntimeException(
                sprintf('Error when crawling the page: %s (%s)', curl_error($curl), curl_errno($curl))
            );
        }

        dump($output);

        return $output;
    }
}

<?php

namespace App\Extractor;

class UrlCrawler4
{
    const MAX_REDIRECT = 30;
    private $numRedirect = 0;

    public function crawlUrl($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $url = str_replace($host, idn_to_ascii($host), $url);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'] )) {
            throw new \UnexpectedValueException('Wrong URL');
        }

        // Looks like an ip
        if (
            preg_match('/^((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?1)){3}\z/', $host)
            || preg_match('/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\dA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?7)){3})\z/i', $host)
        ) {
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw new \UnexpectedValueException('Wrong ip');
            }
        } elseif (filter_var($host, FILTER_VALIDATE_DOMAIN) === false) {
            throw new \UnexpectedValueException('Wrong host');
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false); // On ne suit plus les redirections
        $output = curl_exec($curl);
        if (curl_errno($curl) > 0) {
            throw new \RuntimeException(
                sprintf('Error when crawling the page: %s (%s)', curl_error($curl), curl_errno($curl))
            );
        }

        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($status > 300 && $status < 310 && $status !== 304) {
            // This is a redirect, we want to check everything
            if ($this->numRedirect >= self::MAX_REDIRECT) {
                throw new \RuntimeException('Error, loop detected');
            }
            $this->numRedirect++;
            return $this->crawlUrl(curl_getinfo($curl, CURLINFO_REDIRECT_URL));
        }

        dump($output);

        return $output;
    }
}

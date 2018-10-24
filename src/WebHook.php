<?php

namespace App;

use App\Entity\Link;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class WebHook
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function push(Link $link)
    {
        $item = $this->cache->getItem('webhooks');

        if (!$item->isHit()) {
            return ;
        }

        $webhooks = $item->get();
        $data = json_encode($link->toArray());

        foreach ($webhooks as $webhook) {
            $curl = curl_init($webhook['url']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'X-Token: ' . $webhook['token'],
                    'Content-Length: ' . strlen($data))
            );

            $output = curl_exec($curl);
            dump($output);
            if (curl_errno($curl) > 0) {
                dump(curl_error($curl));
            }
        }
    }
}

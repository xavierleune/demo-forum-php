<?php
$url = urlencode($argv[1]);
$payload = urlencode(file_get_contents($argv[2]));

echo "curl 'http://demo-forum-php/links/webhook' -H 'Origin: http://demo-forum-php' -H 'Content-Type: application/x-www-form-urlencoded' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8' -H 'Referer: http://demo-forum-php/links/webhook' -H 'Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7' --data 'form%5Burl%5D=${url}&form%5Bsave%5D=&form%5Btoken%5D=${payload}'";

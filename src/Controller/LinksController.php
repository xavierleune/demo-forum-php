<?php

namespace App\Controller;

use App\ElasticSearch\DocumentBuilder;
use App\ElasticSearch\LinkFactory;
use App\Extractor\DataExtractor;
use App\WebHook;
use Elastica\Client;
use Elastica\Query;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LinksController extends AbstractController
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var WebHook
     */
    private $webHook;

    public function __construct(CacheItemPoolInterface $cache, WebHook $webHook)
    {
        $this->cache = $cache;
        $this->webHook = $webHook;
    }

    /**
     * @Route("/links", name="link_index")
     * @param Client $client
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Client $client, LinkFactory $linkFactory)
    {
        $elasticaQuery = new Query();
        $elasticaQuery->addSort(['date' => 'desc']);
        $foundLinks = $client->getIndex('forumphp')->search($elasticaQuery);

        $links = function() use ($foundLinks, $linkFactory) {
            foreach ($foundLinks as $link) {
                yield $linkFactory->getLink($link);
            }
        };

        return $this->render('links/index.html.twig', ['links' => $links()]);
    }

    /**
     * @Route("/links/add", name="link_add")
     * @param Request $request
     * @param DataExtractor $dataExtractor
     * @param Client $client
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function add(Request $request, DataExtractor $dataExtractor, Client $client, DocumentBuilder $documentBuilder)
    {
        $form = $this->createFormBuilder()
            ->add('step', ChoiceType::class, ['choices' => array_combine(range(1, 6), range(1, 6))])
            ->add('url', UrlType::class)
            ->add('save', SubmitType::class, ['label'=>'Valider'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $url = $form->getData()['url'];
            $step = (int)$form->getData()['step'];

            try {
                $link = $dataExtractor->extractLinkFromUrl($url, $step);

                if ($link === false) {
                    $this->addFlash('warning', 'Le lien spécifié n\'est pas valide.');
                } else {
                    $client->getIndex('forumphp')->addDocuments([$documentBuilder->getDocumentFromLink($link)]);
                    $client->refreshAll();
                    $this->addFlash('success', 'Article enregistré');
                    $this->webHook->push($link);
                    return $this->redirectToRoute('link_index');
                }

            } catch (\RuntimeException $e) {
                $this->addFlash('danger', sprintf('Une erreur est survenue (demo: "%s")', $e->getMessage()));
            }
        }

        return $this->render('links/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/links/webhook", name="link_webhook")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function webHookAction(Request $request)
    {
        $item = $this->cache->getItem('webhooks');
        $webHooks = [];
        if ($item->isHit()) {
            $webHooks = $item->get();
        }

        $deleteForm = $this->createFormBuilder()
            ->add('delete', SubmitType::class, ['label' => 'Supprimer tous les webhooks'])
            ->getForm()
        ;
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $item->set([]);
            $this->cache->save($item);
            $this->addFlash('success', 'Tous les webhooks ont été supprimés');
            return $this->redirect($this->generateUrl('link_webhook'));
        }

        $form = $this->createFormBuilder(null, ['csrf_protection' => false]) // On désactive la protection pour fluidifier la démo uniquement
            ->add('url', UrlType::class)
            ->add('token', TextType::class, ['trim' => false])
            ->add('save', SubmitType::class, ['label'=>'Enregistrer ce webhook'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $url = $form->getData()['url'];
            $token = $form->getData()['token'];
            $webHooks[] = ['token' => $token, 'url' => $url];
            $item->set($webHooks);
            $this->cache->save($item);
        }

        return $this->render('links/webhook.html.twig', [
            'form' => $form->createView(),
            'deleteForm' => $deleteForm->createView(),
            'webhooks' => $webHooks
        ]);
    }
}

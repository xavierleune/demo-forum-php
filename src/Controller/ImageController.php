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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    /**
     * @Route("/image/upload", name="image_upload")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function upload(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('file', UrlType::class)
            ->add('save', SubmitType::class, ['label'=>'Valider'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->getData()['file'];

            try {
                $uploadDest = '/var/www/demo/var/cache/dev/upload.png';
                if (file_exists($uploadDest)) {
                    unlink($uploadDest);
                }
                $curl = curl_init($file);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                file_put_contents($uploadDest, curl_exec($curl));

                $size = getimagesize($uploadDest);
                if ( ($size[0] * $size[1]) > 50000) { // Valeur à définir
                    throw new \RuntimeException('Image too big');
                }

                $newWidth = 500;
                $newHeight = 500;

                $imagick = new \Imagick(realpath($uploadDest));

                $imagick->scaleImage($newWidth, $newHeight, true);
                $imagick->writeImage('/var/www/demo/public/resized.png');
                $imagick->destroy();

                return $this->render('image/show.html.twig');
            } catch (\ImagickException $e) {
                $this->addFlash('danger', sprintf('Une erreur est survenue (demo: "%s")', $e->getMessage()));
            }
        }

        return $this->render('image/upload.html.twig', ['form' => $form->createView()]);
    }
}

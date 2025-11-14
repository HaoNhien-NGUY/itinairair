<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(HttpClientInterface $httpClient): Response
    {
        /*
         * home dashboard for user, list all travel plans.
         * */

//        $response = $httpClient->request('GET', 'https://aerodatabox.p.rapidapi.com/flights/number/DL47?withAircraftImage=false&withLocation=false');
//        dump($response->getStatusCode(),
//        // $statusCode = 200
////        $response->getHeaders()['content-type'][0],
//        // $contentType = 'application/json'
//        $response->getContent(),
//        );

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}

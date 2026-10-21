<?php

namespace App\Controller;

use App\Repository\ForecastRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherController extends AbstractController
{
    #[Route('/weather', name: 'app_weather_index')]
    public function index(): Response
    {
        return $this->render('weather/index.html.twig', [
            'controller_name' => 'WeatherController',
        ]);
    }

    #[Route(
        '/weather/{name}/{country}',
        name: 'app_weather',
        requirements: ['country' => '[A-Za-z]{2}'],
        defaults: ['country' => null]
    )]
    public function name(
        string $name,
        ForecastRepository $repository,
        LocationRepository $locations,
        ?string $country = null,
    ): Response {
        $location = $locations->findByCityAndCountry($name, $country);

        if ($location === null) {
            throw $this->createNotFoundException('Location not found.');
        }

        return $this->render('weather/name.html.twig', [
            'location' => $location,
            'forecasts' => $repository->findByLocation($location),
        ]);
    }
}

<?php

namespace App\Controller;

use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Security\Http\Attribute\IsGranted;


final class WeatherController extends AbstractController
{
    #[Route('/weather', name: 'app_weather_index')]
    #[IsGranted('ROLE_WEATHER_INDEX')]
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
        WeatherUtil $util,
        LocationRepository $locations,
        ?string $country = null,
    ): Response {
        $location = $locations->findByCityAndCountry($name, $country);

        if ($location === null) {
            throw $this->createNotFoundException('Location not found.');
        }

        $forecasts = $country === null
            ? $util->getWeatherForLocation($location)
            : $util->getWeatherForCountryAndCity($country, $name);

        return $this->render('weather/name.html.twig', [
            'location' => $location,
            'forecasts' => $forecasts,
        ]);
    }
}

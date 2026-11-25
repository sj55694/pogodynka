<?php

namespace App\Controller;

use App\Entity\Forecast;
use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherApiController extends AbstractController
{
    #[Route('/api/v1/weather', name: 'app_weather_api')]
    public function index(
        WeatherUtil $weatherUtil,
        #[MapQueryParameter] string $country,
        #[MapQueryParameter] string $name,
        #[MapQueryParameter] string $format = 'json',
        #[MapQueryParameter('twig')] bool $twig = false,
    ): Response {
        if (!in_array($format, ['json', 'csv'], true)) {
            throw new BadRequestHttpException('Format must be either "json" or "csv".');
        }

        $measurements = $weatherUtil->getWeatherForCountryAndCity($country, $name);

        $formattedMeasurements = array_map(
            static fn (Forecast $measurement) => [
                'date' => $measurement->getDate()?->format('Y-m-d'),
                'celsius' => $measurement->getTemperature(),
                'fahrenheit' => $measurement->getFahrenheit(),
            ],
            $measurements,
        );

        if ($twig) {
            $template = sprintf('weather_api/index.%s.twig', $format);
            $contentType = $format === 'json'
                ? 'application/json; charset=UTF-8'
                : 'text/csv; charset=UTF-8';
            $response = new Response();
            $response->headers->set('Content-Type', $contentType);

            if ($format === 'csv') {
                $response->headers->set(
                    'Content-Disposition',
                    'attachment; filename="weather.csv"',
                );
            }

            return $this->render($template, [
                'city' => $name,
                'country' => $country,
                'measurements' => $formattedMeasurements,
            ], $response);
        }

        if ($format === 'json') {
            return $this->json([
                'name' => $name,
                'country' => $country,
                'measurements' => $formattedMeasurements,
            ]);
        }

        $escapeCsv = static fn (string $value): string => preg_match('/[,"\r\n]/', $value)
            ? '"'.str_replace('"', '""', $value).'"'
            : $value;
        $rows = [implode(',', ['city', 'country', 'date', 'celsius', 'fahrenheit'])];

        foreach ($formattedMeasurements as $measurement) {
            $rows[] = implode(',', array_map($escapeCsv, [
                $name,
                $country,
                $measurement['date'] ?? '',
                (string) ($measurement['celsius'] ?? ''),
                (string) ($measurement['fahrenheit'] ?? ''),
            ]));
        }

        return new Response(implode("\r\n", $rows)."\r\n", Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="weather.csv"',
        ]);
    }
}

<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Forecast;
use App\Entity\Location;
use App\Repository\ForecastRepository;
use App\Repository\LocationRepository;

class WeatherUtil
{
    public function __construct(
        private LocationRepository $locations,
        private ForecastRepository $forecasts,
    ) {}

    /**
     * @return Forecast[]
     */
    public function getWeatherForLocation(Location $location): array
    {
        return $this->forecasts->findByLocation($location);
    }

    /**
     * @return Forecast[]
     */
    public function getWeatherForCountryAndCity(
        string $countryCode,
        string $cityName,
    ): array {
        $location = $this->locations->findByCityAndCountry(
            $cityName,
            $countryCode,
        );

        if ($location === null) {
            return [];
        }

        return $this->getWeatherForLocation($location);
    }
}

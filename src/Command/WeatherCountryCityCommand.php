<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:country-city',
    description: 'Displays forecast for a country code and city name',
)]
class WeatherCountryCityCommand
{
    public function __construct(
        private LocationRepository $locationRepository,
        private WeatherUtil $weatherUtil,
    ) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Country code')] string $countryCode,
        #[Argument('City name')] string $cityName,
    ): int {
        $location = $this->locationRepository->findByCityAndCountry(
            $cityName,
            $countryCode,
        );

        if ($location === null) {
            $io->error(sprintf(
                'Location %s, %s was not found.',
                $cityName,
                strtoupper($countryCode),
            ));

            return Command::FAILURE;
        }

        $measurements = $this->weatherUtil->getWeatherForCountryAndCity(
            $countryCode,
            $cityName,
        );

        $io->writeln(sprintf(
            'Location: %s, %s',
            $location->getName(),
            $location->getCountry(),
        ));

        if ($measurements === []) {
            $io->warning('No forecasts available.');
            return Command::SUCCESS;
        }

        $io->table(
            ['Date', 'Temperature', 'Humidity', 'Pressure', 'Rain'],
            array_map(static fn ($measurement) => [
                $measurement->getDate()?->format('Y-m-d'),
                $measurement->getTemperature().' °C',
                $measurement->getHumidity().'%',
                $measurement->getAirPressure().' hPa',
                $measurement->getRainProbability().'%',
            ], $measurements),
        );

        return Command::SUCCESS;
    }
}

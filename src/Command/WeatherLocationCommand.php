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
    name: 'weather:location',
    description: 'Displays forecast for a location ID',
)]
class WeatherLocationCommand
{
    public function __construct(
        private LocationRepository $locationRepository,
        private WeatherUtil $weatherUtil,
    ) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Location ID')] int $id,
    ): int {
        $location = $this->locationRepository->find($id);

        if ($location === null) {
            $io->error(sprintf('Location with ID %d was not found.', $id));
            return Command::FAILURE;
        }

        $measurements = $this->weatherUtil->getWeatherForLocation($location);

        $io->writeln(sprintf(
            'Location: %s',
            $location->getName(),
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

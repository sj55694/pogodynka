<?php

namespace App\Tests\Entity;

use App\Entity\Forecast;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MeasurementTest extends TestCase
{
    public static function dataGetFahrenheit(): array
    {
        return [
            ['0', '32'],
            ['-100', '-148'],
            ['100', '212'],
            ['0.5', '32.9'],
            ['-0.5', '31.1'],
            ['1.5', '34.7'],
            ['-1.5', '29.3'],
            ['10.5', '50.9'],
            ['-10.5', '13.1'],
            ['37.5', '99.5'],
        ];
    }

    #[DataProvider('dataGetFahrenheit')]
    public function testGetFahrenheit(string $celsius, string $expectedFahrenheit): void
    {
        $forecast = new Forecast();
        $forecast->setTemperature((float) $celsius);

        $this->assertEqualsWithDelta(
            (float) $expectedFahrenheit,
            $forecast->getFahrenheit(),
            1e-10,
        );
    }
}

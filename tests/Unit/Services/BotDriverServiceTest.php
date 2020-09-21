<?php

namespace Tests\Unit\Services;

use App\Enums\DriverType;
use App\Exceptions\InvalidBotDriver;
use App\Services\BotDriverService;
use PHPUnit\Framework\TestCase;

class BotDriverServiceTest extends TestCase
{
    /** @var BotDriverService $service */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(BotDriverService::class);
    }

    /** @test */
    public function driverTypeCanBeSetToGcode()
    {
        $this->service->driver_type = DriverType::GCODE;

        $this->assertEquals(DriverType::GCODE, $this->service->driver_type);
    }

    /** @test */
    public function driverTypeCanBeSetToDummy()
    {
        $this->service->driver_type = DriverType::DUMMY;

        $this->assertEquals(DriverType::DUMMY, $this->service->driver_type);
    }

    /** @test */
    public function driverTypeCannotBeSetToSomethingElse()
    {
        $this->expectException(InvalidBotDriver::class);

        $this->service->driver_type = "foo";
    }

    /** @test */
    public function serialPortCanBeSet()
    {
        $serialPort = '/dev/ttyACM0';

        $this->service->serial_port = $serialPort;

        $this->assertEquals($serialPort, $this->service->serial_port);
    }

    /** @test */
    public function baudRateCanBeSet()
    {
        $baudRate = 115200;

        $this->service->baud_rate = $baudRate;

        $this->assertEquals($baudRate, $this->service->baud_rate);
    }

    /** @test */
    public function commandDelayCanBeSet()
    {
        $this->service->command_delay = 1;

        $this->assertEquals(1, $this->service->command_delay);
    }

    /** @test */
    public function commandDelayMustBeAtLeastZero()
    {
        $this->service->command_delay = -1;

        $this->assertEquals(0, $this->service->command_delay);
    }

    /** @test */
    public function commandDelayIsByDefaultNullForGCodeDriver()
    {
        $this->service->driver_type = DriverType::GCODE;

        $this->assertNull($this->service->command_delay);
    }

    /** @test */
    public function commandDelayIsByDefaultZeroForDummyDriver()
    {
        $this->service->driver_type = DriverType::DUMMY;

        $this->assertNotNull($this->service->command_delay);
        $this->assertEquals(0, $this->service->command_delay);
    }

    /** @test */
    public function unsetDriverEncodesAsNull()
    {
        $this->assertNull($this->service->encode());
    }

    public function canEncodeNullService()
    {
        $this->assertNull($this->service->encode());
    }

    /** @test */
    public function canEncodeGCodeDriver()
    {
        $this->service->driver_type = DriverType::GCODE;
        $this->service->serial_port = $serialPort = '/dev/ttyACM0';
        $this->service->baud_rate = $baudRate = 115200;

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'type' => DriverType::GCODE,
                'config' => [
                    'connection' => [
                        'port' => $serialPort,
                        'baud' => $baudRate,
                    ],
                ],
            ]),
            $this->service->encode()
        );
    }

    /** @test */
    public function canEncodeDummyDriver()
    {
        $this->service->driver_type = DriverType::DUMMY;
        $this->service->command_delay = $commandDelay = 5;

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'type' => DriverType::DUMMY,
                'config' => [
                    'command_delay' => $commandDelay,
                ],
            ]),
            $this->service->encode()
        );
    }

    /** @test */
    public function decodingNullDoesNothing()
    {
        $this->service->decode(null);

        $this->assertNull($this->service->driver_type);
        $this->assertNull($this->service->serial_port);
        $this->assertNull($this->service->baud_rate);
        $this->assertNull($this->service->command_delay);
    }

    /** @test */
    public function canDecodeGCodeDriver()
    {
        $serialPort = '/dev/ttyACM0';
        $baudRate = 115200;

        $encoded = json_encode([
            'type' => DriverType::GCODE,
            'config' => [
                'connection' => [
                    'port' => $serialPort,
                    'baud' => $baudRate,
                ],
            ],
        ]);

        $this->service->decode($encoded);

        $this->assertEquals(DriverType::GCODE, $this->service->driver_type);
        $this->assertEquals($serialPort, $this->service->serial_port);
        $this->assertEquals($baudRate, $this->service->baud_rate);
    }

    /** @test */
    public function canDecodeDummyDriver()
    {
        $commandDelay = 5;

        $encoded = json_encode([
            'type' => DriverType::DUMMY,
            'config' => [
                'command_delay' => $commandDelay,
            ],
        ]);

        $this->service->decode($encoded);

        $this->assertEquals(DriverType::DUMMY, $this->service->driver_type);
        $this->assertEquals($commandDelay, $this->service->command_delay);
    }
}

<?php

namespace App\Services;


use App\Enums\DriverType;
use App\Exceptions\InvalidBotDriver;
use Illuminate\Support\Str;

/**
 * Class BotDriverService
 * @package App\Services
 * @property string driver_type
 * @property string serial_port
 * @property int baud_rate
 * @property float command_delay;
 */
class BotDriverService
{
    protected $driverType;
    protected $serialPort;
    protected $baudRate;
    protected $commandDelay;

    public function encode()
    {
        if($this->driver_type == DriverType::GCODE) {
            return json_encode([
                'type' => DriverType::GCODE,
                'config' => [
                    'connection' => [
                        'port' => $this->serial_port,
                        'baud' => $this->baud_rate,
                    ],
                ],
            ]);
        } else if ($this->driver_type == DriverType::DUMMY) {
            return json_encode([
                'type' => DriverType::DUMMY,
                'config' => [
                    'command_delay' => $this->command_delay,
                ],
            ]);
        }

        return null;
    }

    public function decode($encoded)
    {
        if(is_null($encoded)) {
            return;
        }

        $data = json_decode($encoded, true);

        $this->driver_type = $data['type'];

        if($this->driver_type == DriverType::GCODE) {
            $this->serial_port = $data['config']['connection']['port'];
            $this->baud_rate = $data['config']['connection']['baud'];
        } else if($this->driver_type == DriverType::DUMMY) {
            $this->command_delay = $data['config']['command_delay'];
        }
    }

    protected function getDriverType()
    {
        return $this->driverType;
    }

    /**
     * @param $driverType
     * @throws InvalidBotDriver
     */
    protected function setDriverType($driverType)
    {
        if(!DriverType::allDrivers()->contains($driverType)) {
            throw new InvalidBotDriver("$driverType is not a valid driver");
        }

        $this->driverType = $driverType;
    }

    protected function getSerialPort()
    {
        return $this->serialPort;
    }

    protected function setSerialPort($serialPort)
    {
        $this->serialPort = $serialPort;
    }

    protected function getBaudRate()
    {
        return $this->baudRate;
    }

    protected function setBaudRate($baudRate)
    {
        $this->baudRate = $baudRate;
    }

    protected function getCommandDelay()
    {
        if($this->driver_type == DriverType::DUMMY && is_null($this->commandDelay)) {
            return 0;
        }
        return $this->commandDelay;
    }

    protected function setCommandDelay($commandDelay)
    {
        $this->commandDelay = max(0, $commandDelay);
    }

    public function __get($name)
    {
        $method = "get" . Str::ucfirst(Str::camel($name));

        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return null;
    }

    public function __set($name, $value)
    {
        $method = "set" . Str::ucfirst(Str::camel($name));

        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }
}
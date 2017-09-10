<?php

namespace Tests\Feature\Api\V2;

use App\Enums\ClientRequestStatusEnum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ClientRequestTest extends TestCase
{
    /** @var $faker \Faker\Generator */
    private $faker;
    private $localIpv4;
    private $ipv4;
    private $hostname;

    public function setUp()
    {
        $this->faker = \Faker\Factory::create();
        $this->localIpv4 = $this->faker->localIpv4;
        $this->ipv4 = $this->faker->ipv4;
        $this->hostname = $this->faker->domainWord;

        parent::setUp();
    }

    public function testClientRequestHasStatusOfRequested() {
        $response = $this->json('POST', '/api/v2/client/request', [
            'local_ip' => $this->localIpv4,
            'remote_ip' => $this->ipv4,
            'hostname' => $this->hostname,
        ]);

        $response->assertJson([
            'status' => ClientRequestStatusEnum::Requested,
            'local_ip' => $this->localIpv4,
            'remote_ip' => $this->ipv4,
            'hostname' => $this->hostname,
        ]);
    }

    public function testNoInformationIsNeededForRequest()
    {
        $response = $this->json('POST', '/api/v2/client/request');

        $response->assertJson([
            'status' => ClientRequestStatusEnum::Requested,
            'local_ip' => null,
            'remote_ip' => null,
            'hostname' => null,
        ]);
    }
}

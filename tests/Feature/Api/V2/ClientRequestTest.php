<?php

namespace Tests\Feature\Api\V2;

use App\Enums\ClientRequestStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRequestTest extends TestCase
{
    use RefreshDatabase;

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
            'data' => [
                'status' => ClientRequestStatusEnum::Requested
            ]
        ]);
    }

    public function testNoInformationIsNeededForRequest()
    {
        $response = $this->json('POST', '/api/v2/client/request');

        $response->assertJson([
            'data' => [
                'status' => ClientRequestStatusEnum::Requested
            ]
        ]);
    }
}

<?php

namespace Tests\Feature\Web;

use App\Cluster;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\HasCluster;
use Tests\HasUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClustersTest extends TestCase
{
    use HasUser;
    use HasCluster;
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function unauthenticatedUserIsRedirectedToLogin()
    {
        $this->get('/clusters')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticatedUserSeesClusters()
    {
        $clusters = $this->user->clusters;

        $response = $this->actingAs($this->user)
            ->get('/clusters');

        $clusters->each(function ($cluster) use ($response) {
            $response->assertSee($cluster->name);
        });
    }

    /** @test */
    public function unauthenticatedUserCannotSeeCreateClusterPage()
    {
        $this->get('/clusters/create')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticatedUserSeesCreateClusterPage()
    {
        $this->actingAs($this->user)
            ->get('/clusters/create')
            ->assertViewIs('cluster.create')
            ->assertSee('<input name="name"');
    }

    /** @test */
    public function unauthenticatedUserCannotCreateCluster()
    {
        $this->post('/clusters')
            ->assertRedirect('/login');
    }

    protected function postCluster($overrides = [])
    {
        $default = [
            'name' => $this->faker->userName,
        ];

        return $this->post('/clusters', array_merge($default, $overrides));
    }

    /** @test */
    public function authenticatedUserCanCreateCluster()
    {
        $clusterName = $this->faker->userName;
        $response = $this->actingAs($this->user)
            ->postCluster(['name' => $clusterName]);

        $cluster = Cluster::whereCreatorId($this->user->id)->where('name', $clusterName)->first();
        $this->assertNotNull($cluster);
        $response->assertRedirect("/clusters/{$cluster->id}");
    }
}

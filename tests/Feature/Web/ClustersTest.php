<?php

namespace Tests\Feature\Web;

use App\Cluster;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClustersTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function unauthenticatedUserIsRedirectedToLogin()
    {
        $this
            ->withExceptionHandling()
            ->get('/clusters')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticatedUserSeesClusters()
    {
        $clusters = $this->mainUser->clusters;

        $response = $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->get('/clusters');

        $clusters->each(function ($cluster) use ($response) {
            $response->assertSee($cluster->name);
        });
    }

    /** @test */
    public function unauthenticatedUserCannotSeeCreateClusterPage()
    {
        $this
            ->withExceptionHandling()
            ->get('/clusters/create')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticatedUserSeesCreateClusterPage()
    {
        $this
            ->actingAs($this->mainUser)
            ->get('/clusters/create')
            ->assertViewIs('cluster.create')
            ->assertSee('<input name="name"', $escaped=false);
    }

    /** @test */
    public function unauthenticatedUserCannotCreateCluster()
    {
        $this
            ->withExceptionHandling()
            ->post('/clusters')
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
        $response = $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->postCluster(['name' => $clusterName]);

        $cluster = Cluster::whereCreatorId($this->mainUser->id)->where('name', $clusterName)->first();
        $this->assertNotNull($cluster);
        $response->assertRedirect("/clusters/{$cluster->id}");
    }
}

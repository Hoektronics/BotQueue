<?php

namespace Tests\Feature\Web;

use App\Bot;
use App\Cluster;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\HasUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BotsTest extends TestCase
{
    use HasUser;
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function unauthenticatedUserCannotSeeBotsPage()
    {
        $this->get('/bots')
            ->assertRedirect('/login');
    }

    /** @test */
    public function userWithNoBotsSeesHelpfulMessage()
    {
        $this->actingAs($this->user)
            ->get('/bots')
            ->assertSee('Click the "Create a Bot" button');
    }

    /** @test */
    public function userWithABotSeesThatBot()
    {
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get('/bots')
            ->assertSee($bot->name);
    }

    /** @test */
    public function unauthenticatedUserCannotSeeBotCreationPage()
    {
        $this->get('/bots/create')
            ->assertRedirect('/login');
    }

    /** @test */
    public function userCanSeeBotCreationPage()
    {
        $this->actingAs($this->user)
            ->get('/bots/create')
            ->assertSee('<input name="name"')
            ->assertSee('<select name="type"')
            ->assertSee('<option value="3d_printer">3D Printer</option>')
            ->assertSee('<select name="cluster"');
    }

    /** @test */
    public function unauthenticatedUserCannotCreateBot()
    {
        $this->post('/bots')
            ->assertRedirect('/login');
    }

    /** @test */
    public function userCanCreateBot()
    {
        $cluster = factory(Cluster::class)->create([
            'creator_id' => $this->user,
        ]);

        $botName = $this->faker->name;
        $response = $this->actingAs($this->user)
            ->post('/bots', [
                'name' => $botName,
                'type' => '3d_printer',
                'cluster' => $cluster->id,
            ]);

        $bot = Bot::whereName($botName)->first();
        $response->assertRedirect("/bots/{$bot->id}");
    }
}

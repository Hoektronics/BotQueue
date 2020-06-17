<?php

namespace Tests\Feature\Web;

use App\Bot;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class BotsTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function unauthenticatedUserCannotSeeBotsPage()
    {
        $this
            ->withExceptionHandling()
            ->get('/bots')
            ->assertRedirect('/login');
    }

    /** @test */
    public function userWithNoBotsSeesHelpfulMessage()
    {
        $this
            ->actingAs($this->mainUser)
            ->get('/bots')
            ->assertSee('Click the "Create a Bot" button');
    }

    /** @test */
    public function userWithABotSeesThatBot()
    {
        $bot = $this->bot()->create();

        $this
            ->actingAs($this->mainUser)
            ->get('/bots')
            ->assertSee(e($bot->name));
    }

    /** @test */
    public function unauthenticatedUserCannotSeeBotCreationPage()
    {
        $this
            ->withExceptionHandling()
            ->get('/bots/create')
            ->assertRedirect('/login');
    }

    /** @test */
    public function userCanSeeBotCreationPage()
    {
        $this
            ->actingAs($this->mainUser)
            ->get('/bots/create')
            ->assertViewIs('bot.create')
            ->assertSee('<input name="name"')
            ->assertSee('<select name="type"')
            ->assertSee('<option value="3d_printer">3D Printer</option>')
            ->assertSee('<select name="cluster"');
    }

    /** @test */
    public function unauthenticatedUserCannotCreateBot()
    {
        $this
            ->withExceptionHandling()
            ->post('/bots')
            ->assertRedirect('/login');
    }

    protected function postBot($overrides = [])
    {
        $cluster = $this->cluster()->create();

        $default = [
            'name' => $this->faker->userName,
            'type' => '3d_printer',
            'cluster' => $cluster->id,
        ];

        return $this->post('/bots', array_merge($default, $overrides));
    }

    /** @test */
    public function userCanCreateBot()
    {
        $botName = $this->faker->name;
        $response = $this
            ->actingAs($this->mainUser)
            ->postBot(['name' => $botName]);

        $bot = Bot::whereCreatorId($this->mainUser->id)->where('name', $botName)->first();
        $this->assertNotNull($bot);
        $this->assertNotNull($bot->cluster);
        $response->assertRedirect("/bots/{$bot->id}");
    }

    /** @test */
    public function userCanSeeTheirBot()
    {
        $bot = $this->bot()->create();

        $username = e($this->mainUser->username);

        $this
            ->actingAs($this->mainUser)
            ->get("/bots/{$bot->id}")
            ->assertSee(e($bot->name))
            ->assertSee('Offline')
            ->assertSee("Creator: $username");
    }

    /** @test */
    public function userCanSeeBotsCluster()
    {
        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->cluster($cluster)
            ->create();

        $username = e($this->mainUser->username);

        $this
            ->actingAs($this->mainUser)
            ->get("/bots/{$bot->id}")
            ->assertSee(e($bot->name))
            ->assertSee('Offline')
            ->assertSee("Creator: $username")
            ->assertSee(e($cluster->name));
    }

    /** @test */
    public function anotherUserCannotSeeMyBot()
    {
        $bot = $this->bot()->create();

        $otherUser = $this->user()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($otherUser)
            ->get("/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function aUserCannotMakeABotWithTheSameNameAsAnExistingBot()
    {
        $bot = $this->bot()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->postBot(['name' => $bot->name])
            ->assertSessionHasErrors('name');
    }

    /** @test */
    public function aDifferentUserCanMakeABotWithTheSameNameAsMyExistingBot()
    {
        $bot = $this->bot()->create();

        $otherUser = $this->user()->create();

        $otherCluster = $this->cluster()
            ->creator($otherUser)
            ->create();

        $response = $this
            ->actingAs($otherUser)
            ->postBot([
                'name' => $bot->name,
                'cluster' => $otherCluster->id,
            ]);

        $bot = Bot::whereCreatorId($otherUser->id)->where('name', $bot->name)->first();
        $this->assertNotNull($bot);
        $response->assertRedirect("/bots/{$bot->id}");
    }

    /** @test */
    public function anotherUserCannotAssignABotToMyCluster()
    {
        $otherUser = $this->user()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($otherUser)
            ->postBot()
            ->assertSessionHasErrors('cluster');
    }

    /** @test */
    public function aUserCannotAssignABotToANonExistingCluster()
    {
        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->postBot(['cluster' => 9999])
            ->assertSessionHasErrors('cluster');
    }

    /** @test */
    public function botNameIsRequired()
    {
        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->postBot(['name' => null])
            ->assertSessionHasErrors('name');
    }

    /** @test */
    public function botTypeIsRequired()
    {
        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->postBot(['type' => null])
            ->assertSessionHasErrors('type');
    }

    /** @test */
    public function botClusterIsRequired()
    {
        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->postBot(['cluster' => null])
            ->assertSessionHasErrors('cluster');
    }

    /** @test */
    public function userCanEditTheirOwnBot()
    {
        $bot = $this->bot()->create();

        $this
            ->actingAs($this->mainUser)
            ->get("/bots/{$bot->id}/edit")
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function userCannotEditAnotherUsersBots()
    {
        $bot = $this->bot()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->user()->create())
            ->get("/bots/{$bot->id}/edit")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function userCanUpdateTheirOwnBot()
    {
        $bot = $this->bot()->create();

        $newBotName = 'Some new Name';
        $this
            ->actingAs($this->mainUser)
            ->patch("/bots/{$bot->id}", [
                'name' => $newBotName,
            ])
            ->assertRedirect("/bots/{$bot->id}");

        $bot->refresh();

        $this->assertEquals($newBotName, $bot->name);
    }

    /** @test */
    public function botUpdateCannotHaveEmptyName()
    {
        $bot = $this->bot()->create();
        $originalName = $bot->name;

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->patch("/bots/{$bot->id}", [
                'name' => '',
            ])
            ->assertSessionHasErrors('name');

        $bot->refresh();

        $this->assertEquals($originalName, $bot->name);
    }

    /** @test */
    public function userCannotUpdateAnotherUsersBot()
    {
        $bot = $this->bot()->create();
        $originalName = $bot->name;

        $this
            ->withExceptionHandling()
            ->actingAs($this->user()->create())
            ->patch("/bots/{$bot->id}", [
                'name' => 'Some new Name',
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $bot->refresh();

        $this->assertEquals($originalName, $bot->name);
    }

    /** @test */
    public function userCanChangeDriverToGcodeDriver()
    {
        $bot = $this->bot()->create();

        $this
            ->actingAs($this->mainUser)
            ->patch("/bots/{$bot->id}", [
                'driver' => 'gcode',
                'serial_port' => '/dev/ttyACM0',
                'baud_rate' => 115200,
            ])
            ->assertRedirect("/bots/{$bot->id}");

        $bot->refresh();

        $jsonString = json_encode([
            'type' => 'gcode',
            'config' => [
                'connection' => [
                    'port' => '/dev/ttyACM0',
                    'baud' => 115200,
                ],
            ],
        ]);

        $this->assertEquals($jsonString, $bot->driver);
    }

    /** @test */
    public function serialPortIsRequiredWithGcodeDriver()
    {
        $bot = $this->bot()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->patch("/bots/{$bot->id}", [
                'driver' => 'gcode',
                'baud_rate' => 115200,
            ])
            ->assertSessionHasErrors('serial_port');
    }

    /** @test */
    public function userCanChangeDriverToDummyDriver()
    {
        $bot = $this->bot()->create();

        $this
            ->actingAs($this->mainUser)
            ->patch("/bots/{$bot->id}", [
                'driver' => 'dummy',
            ])
            ->assertRedirect("/bots/{$bot->id}");

        $bot->refresh();

        $jsonString = json_encode([
            'type' => 'dummy',
            'config' => [],
        ]);

        $this->assertEquals($jsonString, $bot->driver);
    }

    /** @test */
    public function dummyDriverCanHaveDelay()
    {
        $bot = $this->bot()->create();

        $this
            ->actingAs($this->mainUser)
            ->patch("/bots/{$bot->id}", [
                'driver' => 'dummy',
                'delay' => 0.01,
            ])
            ->assertRedirect("/bots/{$bot->id}");

        $bot->refresh();

        $jsonString = json_encode([
            'type' => 'dummy',
            'config' => [
                'command_delay' => 0.01,
            ],
        ]);

        $this->assertEquals($jsonString, $bot->driver);
    }

    /** @test */
    public function dummyDriverDelayMustBeANumber()
    {
        $bot = $this->bot()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->patch("/bots/{$bot->id}", [
                'driver' => 'dummy',
                'delay' => 'foo',
            ])
            ->assertSessionHasErrors('delay');
    }

    /** @test */
    public function userCanDeleteTheirBot()
    {
        $bot = $this->bot()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->delete("/bots/{$bot->id}")
            ->assertRedirect('/bots');

        $this->assertDeleted($bot);
    }
}

<?php

namespace Tests\Unit;

use App;
use App\Rules\MatchExists;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\HasUser;
use Tests\TestCase;

class MatchExistsTest extends TestCase
{
    use HasUser;
    use RefreshDatabase;

    /** @test */
    public function matchingOnModelIdAttribute()
    {
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $fieldValue = 'foo_' . $bot->id;
        $fields = [
            'field' => $fieldValue
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Bot::class
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists
        ]);

        $this->assertTrue($validator->passes());
        $this->assertEquals($bot->id, $matchExists->getModel($fieldValue)->id);
    }

    /** @test */
    public function whenNothingMatches()
    {
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $fieldValue = 'foo_' . ($bot->id + 1);
        $fields = [
            'field' => $fieldValue
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Bot::class
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists
        ]);

        $this->assertFalse($validator->passes());
        $this->assertNull($matchExists->getModel($fieldValue));
    }

    /** @test */
    public function multipleFieldMatches()
    {
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $fieldValue = 'bar_' . $bot->id;
        $fields = [
            'field' => $fieldValue
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Bot::class,
            'bar_{id}' => App\Bot::class,
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists
        ]);

        $this->assertTrue($validator->passes());
        $this->assertEquals($bot->id, $matchExists->getModel($fieldValue)->id);
    }

    /** @test */
    public function fieldMatchesWithScope()
    {
        Auth::login($this->user);

        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $fieldValue = 'foo_' . $bot->id;

        $fields = [
            'field' => $fieldValue
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Bot::mine(),
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists
        ]);

        $this->assertTrue($validator->passes());
        $this->assertEquals($bot->id, $matchExists->getModel($fieldValue)->id);
    }
}

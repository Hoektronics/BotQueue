<?php

namespace Tests\Unit;

use App;
use App\Validation\MatchExists;
use Illuminate\Support\Facades\Validator;
use Tests\AuthsUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MatchExistsTest extends TestCase
{
    use AuthsUser;
    use DatabaseMigrations;

    public function testMatchingOnModelIdAttribute()
    {
        $bot = factory(App\Bot::class)->create();

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

    public function testWhenNothingMatches()
    {
        $bot = factory(App\Bot::class)->create();

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

    public function testMultipleFieldMatches()
    {
        $bot = factory(App\Bot::class)->create();

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

    public function testFieldMatchesWithScope()
    {
        $bot = factory(App\Bot::class)->create();

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

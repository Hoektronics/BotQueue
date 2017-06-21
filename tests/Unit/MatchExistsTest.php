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

        $fields = [
            'field' => 'foo_' . $bot->id
        ];

        $validator = Validator::make($fields, [
            'field' => new MatchExists([
                'foo_{id}' => App\Bot::class
            ])
        ]);

        $this->assertTrue($validator->passes());
    }

    public function testWhenNothingMatches()
    {
        $bot = factory(App\Bot::class)->create();

        $fields = [
            'field' => 'foo_' . ($bot->id + 1)
        ];

        $validator = Validator::make($fields, [
            'field' => new MatchExists([
                'foo_{id}' => App\Bot::class
            ])
        ]);

        $this->assertFalse($validator->passes());
    }

    public function testMultipleFieldMatches()
    {
        $bot = factory(App\Bot::class)->create();

        $fields = [
            'field' => 'bar_' . $bot->id
        ];

        $validator = Validator::make($fields, [
            'field' => new MatchExists([
                'foo_{id}' => App\Bot::class,
                'bar_{id}' => App\Bot::class,
            ])
        ]);

        $this->assertTrue($validator->passes());
    }

    public function testFieldMatchesWithScope()
    {
        $bot = factory(App\Bot::class)->create();

        $fields = [
            'field' => 'foo_' . $bot->id
        ];

        $validator = Validator::make($fields, [
            'field' => new MatchExists([
                'foo_{id}' => App\Bot::mine(),
            ])
        ]);

        $this->assertTrue($validator->passes());
    }
}

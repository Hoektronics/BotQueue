<?php

namespace Tests\Unit;

use App;
use App\Bot;
use App\Cluster;
use App\Rules\MatchExists;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\HasBot;
use Tests\HasCluster;
use Tests\HasUser;
use Tests\TestCase;

class MatchExistsTest extends TestCase
{
    use HasUser;
    use HasBot;
    use HasCluster;

    /** @test */
    public function matchingOnModelIdAttribute()
    {
        $fieldValue = 'foo_' . $this->bot->id;
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

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($this->bot->id, $model->id);
        $this->assertInstanceOf(Bot::class, $model);
    }

    /** @test */
    public function whenNothingMatches()
    {
        $fieldValue = 'foo_' . ($this->bot->id + 1);
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
    public function multipleFieldMatchesWithSameModel()
    {
        $fieldValue = 'bar_' . $this->bot->id;
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

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($this->bot->id, $model->id);
        $this->assertInstanceOf(Bot::class, $model);
    }

    /** @test */
    public function multipleFieldMatchesWithDifferentModel()
    {
        $fieldValue = 'bar_' . $this->cluster->id;
        $fields = [
            'field' => $fieldValue
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Bot::class,
            'bar_{id}' => App\Cluster::class,
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists
        ]);

        $this->assertTrue($validator->passes());

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($this->cluster->id, $model->id);
        $this->assertInstanceOf(Cluster::class, $model);
    }

    /** @test */
    public function fieldMatchesWithScope()
    {
        $this->actingAs($this->user);

        $fieldValue = 'foo_' . $this->bot->id;

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

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($this->bot->id, $model->id);
        $this->assertInstanceOf(Bot::class, $model);
    }
}

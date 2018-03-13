<?php

namespace Tests\Unit;

use App;
use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Rules\MatchExists;
use Illuminate\Support\Facades\Validator;
use Tests\HasUser;
use Tests\TestCase;

class MatchExistsTest extends TestCase
{
    use HasUser;

    /** @test */
    public function matchingOnModelIdAttribute()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
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

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($bot->id, $model->id);
        $this->assertInstanceOf(Bot::class, $model);
    }

    /** @test */
    public function whenNothingMatches()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
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
    public function multipleFieldMatchesWithSameModel()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
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

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($bot->id, $model->id);
        $this->assertInstanceOf(Bot::class, $model);
    }

    /** @test */
    public function multipleFieldMatchesWithDifferentModel()
    {
        /** @var Cluster $cluster */
        $cluster = factory(Cluster::class)
            ->create([
                'creator_id' => $this->user,
            ]);

        $fieldValue = 'bar_' . $cluster->id;
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
        $this->assertEquals($cluster->id, $model->id);
        $this->assertInstanceOf(Cluster::class, $model);
    }

    /** @test */
    public function fieldMatchesWithScope()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        $this->actingAs($this->user);

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

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($bot->id, $model->id);
        $this->assertInstanceOf(Bot::class, $model);
    }
}

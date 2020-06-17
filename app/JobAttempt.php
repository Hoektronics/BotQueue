<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\JobAttempt.
 *
 * @property int $id
 * @property int $job_id
 * @property int $bot_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Bot $bot
 * @property-read \App\Job $job
 * @method static \Illuminate\Database\Eloquent\Builder|\App\JobAttempt whereBotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\JobAttempt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\JobAttempt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\JobAttempt whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\JobAttempt whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\JobAttempt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\JobAttempt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\JobAttempt query()
 */
class JobAttempt extends Model
{
    protected $fillable = [
        'bot_id',
        'job_id',
    ];

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}

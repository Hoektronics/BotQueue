<?php

namespace App\Models;

use App\ModelTraits\UuidKey;
use Illuminate\Database\Eloquent\Model;

/**
 * App\JobAttempt.
 *
 * @property string $id
 * @property string $job_id
 * @property string $bot_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Bot $bot
 * @property-read Job $job
 * @method static \Illuminate\Database\Eloquent\Builder|JobAttempt whereBotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobAttempt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobAttempt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobAttempt whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobAttempt whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|JobAttempt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobAttempt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobAttempt query()
 */
class JobAttempt extends Model
{
    use UuidKey;

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

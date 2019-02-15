<?php

namespace App;

use App\Events\JobCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Job
 *
 * @property int $id
 * @property string $name
 * @property string $status
 * @property int $creator_id
 * @property int $worker_id
 * @property string $worker_type
 * @property int|null $bot_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Bot|null $bot
 * @property-read \App\User $creator
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $worker
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job mine()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereBotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereWorkerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereWorkerType($value)
 * @mixin \Eloquent
 * @property int|null $file_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Job whereFileId($value)
 */
class Job extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'creator_id',
        'file_id',
    ];

    protected $dispatchesEvents = [
        'created' => JobCreated::class,
    ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function worker()
    {
        return $this->morphTo();
    }

    public function workerIs($class)
    {
        $modelClass = static::getActualClassNameForMorph($this->worker_type);

        return $modelClass == $class;
    }

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }

    /**
     * Scope to only include jobs belonging to the currently authenticated user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine($query)
    {
        return $query->where('creator_id', Auth::user()->id);
    }
}

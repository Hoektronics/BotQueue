<?php

namespace App;

use App\Enums\BotStatusEnum;
use App\Events\BotCreated;
use App\ModelTraits\BelongsToHostTrait;
use App\ModelTraits\WorksOnJobsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Bot
 *
 * @property int $id
 * @property int $creator_id
 * @property string $name
 * @property string $type
 * @property string|null $seen_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $status
 * @property int|null $host_id
 * @property int|null $current_job_id
 * @property-read \App\Cluster $cluster
 * @property-read \App\User $creator
 * @property-read \App\Job|null $currentJob
 * @property-read \App\Host|null $host
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot mine()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereCurrentJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereHostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Bot extends Model
{
    use WorksOnJobsTrait;
    use BelongsToHostTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'creator_id',
        'cluster_id',
    ];

    protected $dispatchesEvents = [
        'created' => BotCreated::class,
    ];

    protected $attributes = [
        'status' => BotStatusEnum::OFFLINE,
    ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    public function currentJob()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Scope to only include bots belonging to the currently authenticated user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine($query)
    {
        return $query->where('creator_id', Auth::user()->id);
    }
}

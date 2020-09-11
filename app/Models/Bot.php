<?php

namespace App\Models;

use App\Enums\BotStatusEnum;
use App\Events\BotCreated;
use App\ModelTraits\BelongsToHostTrait;
use App\ModelTraits\WorksOnJobsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Bot.
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
 * @property int|null $cluster_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereClusterId($value)
 * @property string|null $driver
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereDriver($value)
 * @property string|null $error_text
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereErrorText($value)
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

    protected $casts = [
        'driver' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    public function host()
    {
        return $this->belongsTo(Host::class);
    }

    public function currentJob()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Scope to only include bots belonging to the currently authenticated user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine($query)
    {
        return $query->where('creator_id', Auth::user()->id);
    }
}

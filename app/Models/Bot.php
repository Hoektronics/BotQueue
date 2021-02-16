<?php

namespace App\Models;

use App\Enums\BotStatusEnum;
use App\Events\BotCreated;
use App\Events\BotDeleted;
use App\Events\BotUpdated;
use App\ModelTraits\BelongsToHostTrait;
use App\ModelTraits\UuidKey;
use App\ModelTraits\WorksOnJobsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Bot.
 *
 * @property string $id
 * @property string $creator_id
 * @property string $name
 * @property string $type
 * @property string $status
 * @property string|null $error_text
 * @property string|null $seen_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $host_id
 * @property string $cluster_id
 * @property array|null $driver
 * @property bool $job_available
 * @property string|null $current_job_id
 * @property-read \App\Models\Cluster $cluster
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\Job|null $currentJob
 * @property-read \App\Models\Host|null $host
 * @method static \Illuminate\Database\Eloquent\Builder|Bot mine()
 * @method static \Illuminate\Database\Eloquent\Builder|Bot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bot query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereClusterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereCurrentJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereErrorText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereHostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereJobAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bot whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Bot extends Model
{
    use UuidKey;
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
        'updated' => BotUpdated::class,
        'deleted' => BotDeleted::class,
    ];

    protected $attributes = [
        'status' => BotStatusEnum::OFFLINE,
    ];

    protected $casts = [
        'driver' => 'array',
        'job_available' => 'boolean',
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

    public function getJobAvailableAttribute($value): bool
    {
        return $value ?? false;
    }
}

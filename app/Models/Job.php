<?php

namespace App\Models;

use App\Events\JobCreated;
use App\Events\JobUpdated;
use App\ModelTraits\UuidKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Job.
 *
 * @property string $id
 * @property string $name
 * @property string $status
 * @property string $creator_id
 * @property string $worker_id
 * @property string $worker_type
 * @property string|null $bot_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $file_id
 * @property float $progress
 * @property-read \App\Models\Bot|null $bot
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\File|null $file
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read int|null $tasks_count
 * @property-read Model|\Eloquent $worker
 * @method static \Illuminate\Database\Eloquent\Builder|Job mine()
 * @method static \Illuminate\Database\Eloquent\Builder|Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job query()
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereBotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereWorkerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereWorkerType($value)
 * @mixin \Eloquent
 */
class Job extends Model
{
    use UuidKey;

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
        'updated' => JobUpdated::class,
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

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Scope to only include jobs belonging to the currently authenticated user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine($query)
    {
        return $query->where('creator_id', Auth::user()->id);
    }
}

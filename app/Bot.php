<?php

namespace App;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\BotCreated;
use App\Events\BotGrabbedJob;
use App\Events\Host\BotAssignedToHost;
use App\Events\Host\BotRemovedFromHost;
use App\Exceptions\BotCannotGrabJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * App\Bot
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $seen_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereSeenAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereUserId($value)
 * @mixin \Eloquent
 * @property int $creator_id
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereCreatorId($value)
 * @property string $status
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereStatus($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Cluster[] $clusters
 * @property-read \App\User $creator
 * @method static \Illuminate\Database\Query\Builder|\App\Bot mine()
 * @property string $type
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereType($value)
 * @property-read \App\Host $host
 * @property int|null $host_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Bot whereHostId($value)
 */
class Bot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'creator_id',
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

    public function clusters()
    {
        return $this->belongsToMany(Cluster::class);
    }

    public function host()
    {
        return $this->belongsTo(Host::class);
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

    public function assignTo($host)
    {
        if ($this->host_id !== null) {
            $oldHost = $this->host;

            event(new BotRemovedFromHost($this, $oldHost));
        }

        $this->host_id = $host->id;

        $this->save();

        event(new BotAssignedToHost($this, $host));
    }

    /**
     * @param $job Job
     * @throws BotCannotGrabJob
     */
    public function grabJob($job)
    {
        if (!$this->canGrab($job))
            throw new BotCannotGrabJob("This job cannot be grabbed!");

        try {
            DB::transaction(function () use ($job) {
                Job::query()
                    ->whereKey($job->getKey())
                    ->where('status', JobStatusEnum::QUEUED)
                    ->whereNull('bot_id')
                    ->update([
                        'bot_id' => $this->id,
                        'status' => JobStatusEnum::ASSIGNED
                    ]);

                $job->refresh();

                if ($job->bot_id != $this->id)
                    throw new BotCannotGrabJob("This job cannot be grabbed!");
            });
        } catch (\Exception|\Throwable $e) {
            throw new BotCannotGrabJob("Unexpected exception while trying to grab job");
        }

        event(new BotGrabbedJob($this, $job));
    }

    /**
     * @param $job Job
     * @return bool
     */
    public function canGrab($job)
    {
        if ($job->worker instanceof Bot && $job->worker->id == $this->id)
            return $job->status == JobStatusEnum::QUEUED;

        if ($job->worker instanceof Cluster && $job->worker->bots->contains($this->id))
            return $job->status == JobStatusEnum::QUEUED;

        return false;
    }
}

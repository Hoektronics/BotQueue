<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Job
 *
 * @property int $id
 * @property string $name
 * @property string $status
 * @property int $creator_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Job whereCreatorId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Job whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Job whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Job whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Job whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $worker_id
 * @property string $worker_type
 * @property int $bot_id
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $worker
 * @method static \Illuminate\Database\Query\Builder|\App\Job whereBotId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Job whereWorkerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Job whereWorkerType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Job mine()
 * @property-read \App\User $creator
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
    ];

    public function creator() {
        return $this->belongsTo(User::class);
    }

    public function worker() {
        return $this->morphTo();
    }

    /**
     * Scope to only include jobs belonging to the currently authenticated user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine($query) {
        return $query->where('creator_id', Auth::user()->id);
    }
}

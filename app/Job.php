<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
 */
class Job extends Model
{
    //
}

<?php

namespace App\Models;

use App\ModelTraits\UuidKey;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Task
 *
 * @property string $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $started_at
 * @property string|null $stopped_at
 * @property string $type
 * @property mixed $data
 * @property string $status
 * @property string $job_id
 * @property string|null $depends_on
 * @property string|null $input_file_id
 * @property string|null $output_file_id
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDependsOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereInputFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereOutputFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStoppedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Task extends Model
{
    use UuidKey;
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * App\File
 *
 * @property int $id
 * @property string $path
 * @property string $filesystem
 * @property string $name
 * @property int $size
 * @property string $type
 * @property int $uploader_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User $uploader
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File whereFilesystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File whereUploaderId($value)
 * @mixin \Eloquent
 */
class File extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'path',
        'filesystem',
        'type',
        'size',
        'uploader_id'
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class);
    }

    public function setPathAttribute($path)
    {
        $this->attributes['path'] = $path;
        $this->attributes['size'] = Storage::size($path);
    }
}

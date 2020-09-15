<?php

namespace App\Models;

use App\Enums\FileTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * App\File.
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\File query()
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
        'uploader_id',
    ];

    /**
     * @param UploadedFile $uploadedFile
     * @param User $uploader
     * @return File
     * @throws \Exception
     */
    public static function fromUploadedFile($uploadedFile, $uploader)
    {
        $clientOriginalName = $uploadedFile->getClientOriginalName();

        $extension = FileFacade::extension($clientOriginalName);
        $hash = Str::random(40);
        $uploadedFilePath = $uploadedFile->storePubliclyAs(
            "uploads/{$hash}",
            $clientOriginalName,
            [
                "disk" => "public",
            ]
        );

        $file = new self([
            'path' => $uploadedFilePath,
            'name' => $clientOriginalName,
            'filesystem' => 'public',
            'type' => FileTypeEnum::fromExtension($extension),
            'uploader_id' => $uploader->id,
        ]);

        $file->save();

        return $file;
    }

    public function uploader()
    {
        return $this->belongsTo(User::class);
    }

    public function setPathAttribute($path)
    {
        $this->attributes['path'] = $path;
        $this->attributes['size'] = Storage::disk('public')->size($path);
    }

    public function url()
    {
        return Storage::disk('public')->url($this->path);
    }
}

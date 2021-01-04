<?php

namespace App\Models;

use App\Enums\FileTypeEnum;
use App\ModelTraits\UuidKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * App\File.
 *
 * @property string $id
 * @property string $path
 * @property string $filesystem
 * @property string $name
 * @property int $size
 * @property string $type
 * @property string $uploader_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read User $uploader
 * @method static \Illuminate\Database\Eloquent\Builder|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereFilesystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUploaderId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File query()
 */
class File extends Model
{
    use UuidKey;

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

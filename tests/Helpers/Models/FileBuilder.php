<?php

namespace Tests\Helpers\Models;

use App\Enums\FileTypeEnum;
use App\Models\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class FileBuilder
{
    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @return File
     */
    public function create()
    {
        $uploadedFile = UploadedFile::fake()->create($this->attributes['name']);
        $uploadedFilePath = $uploadedFile->storePublicly(
            'uploads',
            'public'
        );

        $this->attributes = array_merge($this->attributes, [
            'path' => $uploadedFilePath,
            'filesystem' => 'public',
        ]);

        return File::unguarded(function () {
            return File::create($this->attributes);
        });
    }

    private function newWith($newAttributes)
    {
        return new self(
            array_merge(
                $this->attributes,
                $newAttributes
            )
        );
    }

    public function uploader(User $user)
    {
        return $this->newWith(['uploader_id' => $user->id]);
    }

    public function name(string $name)
    {
        return $this->newWith(['name' => $name]);
    }

    public function createdAt(Carbon $createdAt)
    {
        return $this->newWith(['created_at' => $createdAt]);
    }

    public function stl()
    {
        return $this->newWith(['type' => FileTypeEnum::STL]);
    }

    public function gcode()
    {
        return $this->newWith(['type' => FileTypeEnum::GCODE]);
    }
}

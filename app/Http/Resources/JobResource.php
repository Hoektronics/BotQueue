<?php

namespace App\Http\Resources;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class JobResource
 * @package App\Http\Resources
 *
 * @property int id
 * @property string $name
 * @property string $status
 * @property float progress
 * @property File $file
 */
class JobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "status" => $this->status,
            "progress" => $this->progress,
            "url" => $this->file->url(),
        ];
    }

    public function with($request)
    {
        return [
            "status" => "success",
        ];
    }
}

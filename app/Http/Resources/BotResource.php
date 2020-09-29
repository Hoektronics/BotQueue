<?php

namespace App\Http\Resources;

use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BotResource.
 *
 * @property int $id
 * @property string $name
 * @property string $status
 * @property string $type
 * @property User $creator
 * @property Job $currentJob
 * @property array $driver
 * @property boolean job_available
 */
class BotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'job_available' => $this->job_available,
            'type' => $this->type,
            'driver' => $this->driver,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'username' => $this->creator->username,
                    'link' => url('/api/users', $this->creator->id),
                ];
            }),
            'job' => $this->whenLoaded('currentJob', function () {
                return [
                    'id' => $this->currentJob->id,
                    'name' => $this->currentJob->name,
                    'status' => $this->currentJob->status,
                    'url' => $this->currentJob->file->url(),
                ];
            }),
        ];
    }

    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}

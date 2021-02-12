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
                return new UserResource($this->creator);
            }),
            'job' => $this->whenLoaded('currentJob', function () {
                return new JobResource($this->currentJob);
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

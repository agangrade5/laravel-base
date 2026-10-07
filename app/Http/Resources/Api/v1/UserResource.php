<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\FileUploadService;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'country_iso' => $this->country_iso,
            'country_code' => $this->country_code,
            'phone_number' => $this->phone_number,
            'phone' => $this->country_code . $this->phone_number,
            'profile_image' => $this->image
                ? app(FileUploadService::class)->url($this->image)
                : null,
        ];
    }
}

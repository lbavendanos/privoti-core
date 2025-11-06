<?php

declare(strict_types=1);

namespace App\Http\Cms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CustomerResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => null,
            'phone' => $this->phone,
            'dob' => $this->dob,
            'account' => $this->account,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'addresses' => CustomerAddressResource::collection($this->whenLoaded('addresses')),
        ];
    }
}

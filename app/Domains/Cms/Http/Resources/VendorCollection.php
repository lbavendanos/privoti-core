<?php

namespace App\Domains\Cms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VendorCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'created_at' => $type->created_at,
                    'updated_at' => $type->updated_at,
                ];
            }),
        ];
    }
}

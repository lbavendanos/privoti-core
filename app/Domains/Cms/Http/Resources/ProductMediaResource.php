<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

final class ProductMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'url' => $this->url,
            'name' => $this->name,
            'type' => $this->type,
            'rank' => $this->rank,
            'product_id' => $this->product_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($request->filled('fields')) {
            $fields = explode(',', (string) $request->input('fields'));
            $data = Arr::only($data, $fields);
        }

        return array_merge($data, []);
    }
}

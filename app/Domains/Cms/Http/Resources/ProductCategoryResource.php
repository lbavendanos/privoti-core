<?php

namespace App\Domains\Cms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ProductCategoryResource extends JsonResource
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
            'name' => $this->name,
            'handle' => $this->handle,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_public' => $this->is_public,
            'rank' => $this->rank,
            'metadata' => $this->metadata,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // 'children' => ProductCategoryResource::collection($this->whenLoaded('children')),
            // 'parent' => new ProductCategoryResource($this->whenLoaded('parent')),
            // 'products' => ProductResource::collection($this->whenLoaded('products')),
        ];

        $fields = $request->input('fields');
        $fieldsArray = $fields ? explode(',', $fields) : [];

        $data = !empty($fieldsArray) ? Arr::only($data, $fieldsArray) : $data;

        $data['children'] = ProductCategoryResource::collection($this->whenLoaded('children'));

        return $data;
    }
}

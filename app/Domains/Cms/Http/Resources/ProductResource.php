<?php

namespace App\Domains\Cms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'handle' => $this->handle,
            'description' => $this->description,
            'status' => $this->status,
            'tags' => $this->tags,
            'category_id' => $this->category_id,
            'type_id' => $this->type_id,
            'vendor_id' => $this->vendor_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'type' => new ProductTypeResource($this->whenLoaded('type')),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'media' => ProductMediaResource::collection($this->whenLoaded('media')),
            'options' => ProductOptionResource::collection($this->whenLoaded('options')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}

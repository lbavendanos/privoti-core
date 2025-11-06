<?php

declare(strict_types=1);

namespace App\Http\Cms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
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
            'thumbnail' => $this->thumbnail,
            'stock' => $this->stock,
            'status' => $this->status,
            'tags' => $this->tags,
            'metadata' => $this->metadata,
            'category_id' => $this->category_id,
            'type_id' => $this->type_id,
            'vendor_id' => $this->vendor_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'type' => new ProductTypeResource($this->whenLoaded('type')),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'collections' => CollectionResource::collection($this->whenLoaded('collections')),
            'media' => ProductMediaResource::collection($this->whenLoaded('media')),
            'options' => ProductOptionResource::collection($this->whenLoaded('options')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}

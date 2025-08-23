<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

final class CollectionResource extends JsonResource
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
            'title' => $this->title,
            'handle' => $this->handle,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($request->filled('fields')) {
            $fields = explode(',', $request->string('fields')->value());
            $data = Arr::only($data, $fields);
        }

        /** @var array<string, mixed> $data */
        return array_merge($data, []);
    }
}

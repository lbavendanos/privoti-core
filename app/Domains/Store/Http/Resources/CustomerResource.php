<?php

declare(strict_types=1);

namespace App\Domains\Store\Http\Resources;

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
        /** @phpstan-ignore-next-line */
        return parent::toArray($request);
    }
}

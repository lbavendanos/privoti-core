<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Requests\Vendor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class GetVendorsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string'],
            'created_at' => ['nullable', 'array', 'max:2'],
            'created_at.*' => ['date'],
            'created_at.1' => ['nullable', 'after_or_equal:created_at.0'],
            'updated_at' => ['nullable', 'array', 'max:2'],
            'updated_at.*' => ['date'],
            'updated_at.1' => ['nullable', 'after_or_equal:updated_at.0'],
            'order' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
        ];
    }
}

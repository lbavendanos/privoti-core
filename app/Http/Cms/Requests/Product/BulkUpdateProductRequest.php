<?php

declare(strict_types=1);

namespace App\Http\Cms\Requests\Product;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

final class BulkUpdateProductRequest extends FormRequest
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
            'items' => ['required', 'array'],
            'items.*.id' => ['required', Rule::exists('products', 'id')->withoutTrashed()],
            'items.*.title' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products')->ignore($this->integer('items.*.id'))->withoutTrashed()],
            'items.*.subtitle' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.status' => ['nullable', Rule::in(Product::STATUS_LIST)],
            'items.*.tags' => ['nullable', 'array'],
            'items.*.category_id' => ['nullable', Rule::exists('product_categories', 'id')->where('is_active', true)->withoutTrashed()],
            'items.*.type_id' => ['nullable', Rule::exists('product_types', 'id')->withoutTrashed()],
            'items.*.vendor_id' => ['nullable', Rule::exists('vendors', 'id')->withoutTrashed()],
            'items.*.collections' => ['nullable', 'array'],

            // Media
            'items.*.media' => ['nullable', 'array'],
            'items.*.media.*.id' => ['nullable', Rule::exists('product_media', 'id')->where('product_id', $this->integer('items.*.id'))->withoutTrashed()],
            'items.*.media.*.file' => ['required_without:items.*.media.*.id', File::image()->max('1mb')],
            'items.*.media.*.rank' => ['required_with:items.*.media', 'integer'],

            // Options
            'items.*.options' => ['nullable', 'array'],
            'items.*.options.*.id' => ['nullable', Rule::exists('product_options', 'id')->where('product_id', $this->integer('items.*.id'))->withoutTrashed()],
            'items.*.options.*.name' => ['required_with:items.*.options', 'string', 'max:255'],
            'items.*.options.*.values' => ['nullable', 'array'],
            'items.*.options.*.values.*' => ['required_with:items.*.options.*.values', 'string', 'max:255'],

            // Variants
            'items.*.variants' => ['nullable', 'array'],
            'items.*.variants.*.id' => ['nullable', Rule::exists('product_variants', 'id')->where('product_id', $this->integer('items.*.id'))->withoutTrashed()],
            'items.*.variants.*.name' => ['required_with:items.*.variants', 'string', 'max:255'],
            'items.*.variants.*.price' => ['required_with:items.*.variants', 'numeric'],
            'items.*.variants.*.quantity' => ['required_with:items.*.variants', 'integer'],
            'items.*.variants.*.options' => ['required_with:items.*.variants', 'array'],
            'items.*.variants.*.options.*.value' => ['required_with:items.*.variants.*.options', 'string', 'max:255'],
        ];
    }
}

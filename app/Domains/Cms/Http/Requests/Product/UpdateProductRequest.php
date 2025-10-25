<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

final class UpdateProductRequest extends FormRequest
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
        /** @var Product $product */
        $product = $this->product;

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)->withoutTrashed()],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(Product::STATUS_LIST)],
            'tags' => ['nullable', 'array'],
            'category_id' => ['nullable', Rule::exists('product_categories', 'id')->where('is_active', true)->withoutTrashed()],
            'type_id' => ['nullable', Rule::exists('product_types', 'id')->withoutTrashed()],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->withoutTrashed()],
            'collections' => ['nullable', 'array'],

            // Media
            'media' => ['nullable', 'array'],
            'media.*.id' => ['nullable', Rule::exists('product_media', 'id')->where('product_id', $product->id)->withoutTrashed()],
            'media.*.file' => ['required_without:media.*.id', File::image()->max('1mb')],
            'media.*.rank' => ['required_with:media', 'integer'],

            // Options
            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable', Rule::exists('product_options', 'id')->where('product_id', $product->id)->withoutTrashed()],
            'options.*.name' => ['required_with:options', 'string', 'max:255'],
            'options.*.values' => ['nullable', 'array'],
            'options.*.values.*' => ['required_with:options.*.values', 'string', 'max:255'],

            // Variants
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', Rule::exists('product_variants', 'id')->where('product_id', $product->id)->withoutTrashed()],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric'],
            'variants.*.quantity' => ['required_with:variants', 'integer'],
            'variants.*.options' => ['required_with:variants', 'array'],
            'variants.*.options.*.value' => ['required_with:variants', 'string', 'max:255'],
        ];
    }
}

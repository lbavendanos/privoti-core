<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class EmailChangeVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @phpstan-ignore-next-line */
        if (! hash_equals((string) $this->user()->getKey(), (string) $this->route('id'))) {
            return false;
        }

        return hash_equals(sha1($this->route('email')), (string) $this->route('hash'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}

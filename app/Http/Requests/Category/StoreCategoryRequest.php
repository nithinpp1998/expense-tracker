<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],

            'color'     => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],

            'is_active' => ['boolean'],
        ];
    }
}

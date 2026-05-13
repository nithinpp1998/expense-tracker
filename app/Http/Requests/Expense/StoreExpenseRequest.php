<?php

declare(strict_types=1);

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

final class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'description' => ['required', 'string', 'max:500'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }
}

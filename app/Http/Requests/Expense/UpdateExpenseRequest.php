<?php

declare(strict_types=1);

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'numeric', 'min:0.01', 'max:99999999.99'],
            'description' => ['sometimes', 'string', 'max:500'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'occurred_at' => ['sometimes', 'date', 'before_or_equal:now'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ];
    }
}

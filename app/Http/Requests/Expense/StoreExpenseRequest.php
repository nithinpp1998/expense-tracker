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
            'amount'      => ['required', 'numeric', 'min:'.config('constants.expense.amount_min'), 'max:'.config('constants.expense.amount_max')],
            'description' => ['required', 'string', 'max:'.config('constants.expense.description_max_length')],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
            'currency'    => ['nullable', 'string', 'size:'.config('constants.currency.code_length')],
        ];
    }
}

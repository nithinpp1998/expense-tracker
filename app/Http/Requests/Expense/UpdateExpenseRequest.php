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
            'amount'      => ['sometimes', 'numeric', 'min:'.config('constants.expense.amount_min'), 'max:'.config('constants.expense.amount_max')],
            'description' => ['sometimes', 'string', 'max:'.config('constants.expense.description_max_length')],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'occurred_at' => ['sometimes', 'date', 'before_or_equal:now'],
            'currency'    => ['sometimes', 'string', 'size:'.config('constants.currency.code_length')],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

final class IndexExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'from'        => ['nullable', 'date'],
            'to'          => ['nullable', 'date', 'after_or_equal:from'],
            'search'      => ['nullable', 'string', 'max:'.config('constants.expense.search_max_length')],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:'.config('constants.pagination.max_per_page')],
        ];
    }
}

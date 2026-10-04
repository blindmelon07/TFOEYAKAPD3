<?php

namespace App\Http\Requests\Admin;

use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DuesPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Member $member */
        $member = $this->route('member');

        return $this->user()?->can('manageDues', $member) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:1979', 'max:'.(now()->year + 1)],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999.99', 'decimal:0,2'],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}

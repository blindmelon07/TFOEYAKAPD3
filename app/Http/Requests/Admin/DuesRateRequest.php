<?php

namespace App\Http\Requests\Admin;

use App\Models\Club;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DuesRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Club $club */
        $club = $this->route('club');

        return $this->user()?->can('manageDuesRates', $club) ?? false;
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
        ];
    }
}

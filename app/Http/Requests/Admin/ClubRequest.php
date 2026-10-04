<?php

namespace App\Http\Requests\Admin;

use App\Models\Club;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClubRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Club::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Club|null $club */
        $club = $this->route('club');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Club::class)->ignore($club?->id)],
            'location' => ['nullable', 'string', 'max:255'],
            'charter_number' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'remove_logo' => ['boolean'],
        ];
    }
}

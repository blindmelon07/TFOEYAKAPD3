<?php

namespace App\Http\Requests\Admin;

use App\Models\Club;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ClubDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Club $club */
        $club = $this->route('club');

        return $this->user()?->can('manageDocuments', $club) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'document_date' => ['required', 'date'],
            'body' => ['nullable', 'string', 'max:20000'],
        ];
    }
}

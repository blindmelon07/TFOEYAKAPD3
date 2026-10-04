<?php

namespace App\Http\Requests\Admin;

use App\Models\Club;
use App\Services\LetterheadRenderer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ClubLetterheadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Club $club */
        $club = $this->route('club');

        return $this->user()?->can('manageLetterhead', $club) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'letterhead' => [
                'required',
                'file',
                'extensions:docx',
                'max:10240',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value instanceof UploadedFile && ! LetterheadRenderer::isValidTemplate($value->getRealPath())) {
                        $fail('The letterhead must be a Word (.docx) document.');
                    }
                },
            ],
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'letterhead.extensions' => 'The letterhead must be a Word (.docx) document.',
        ];
    }
}

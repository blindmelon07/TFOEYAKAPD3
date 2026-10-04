<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (array_keys(Setting::defaults()) as $key) {
            $rules[$key] = in_array($key, Setting::IMAGE_KEYS, true)
                ? ['nullable', 'image', 'max:5120']
                : ['nullable', 'string', 'max:5000'];
        }

        $rules['contact_email'] = ['nullable', 'email', 'max:255'];
        $rules['member_portal_url'] = ['nullable', 'string', 'max:2048'];
        $rules['charter_guidelines_url'] = ['nullable', 'string', 'max:2048'];

        return $rules;
    }

    /**
     * Get the submitted text settings, excluding image uploads.
     *
     * @return array<string, string|null>
     */
    public function textSettings(): array
    {
        $textKeys = array_diff(array_keys(Setting::defaults()), Setting::IMAGE_KEYS);

        /** @var array<string, string|null> $values */
        $values = collect($textKeys)
            ->filter(fn (string $key): bool => $this->exists($key))
            ->mapWithKeys(fn (string $key): array => [$key => $this->validated($key)])
            ->all();

        return $values;
    }
}

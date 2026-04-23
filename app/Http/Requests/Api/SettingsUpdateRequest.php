<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SettingsUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'update_interval' => 'integer|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'update_interval.integer' => 'Интервал обновления должен быть целым числом',
            'update_interval.min' => 'Минимальный интервал обновления — 5 секунд',
        ];
    }
}

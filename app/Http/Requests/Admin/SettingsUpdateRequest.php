<?php

namespace App\Http\Requests\Admin;

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
            'update_interval' => 'required|integer|min:5',
            'fetch_currencies' => 'nullable|array',
            'fetch_currencies.*' => 'integer|exists:currencies,id',
            'widget_currencies' => 'nullable|array',
            'widget_currencies.*' => 'integer|exists:currencies,id',
        ];
    }


    public function messages(): array
    {
        return [
            'update_interval.required' => 'Интервал обновления не указан',
            'update_interval.integer' => 'Интервал обновления должен быть целым числом',
            'update_interval.min' => 'Минимальный интервал обновления — 5 секунд',
            'fetch_currencies.*.exists' => 'Одна из выбранных валют не существует',
            'widget_currencies.*.exists' => 'Одна из выбранных валют не существует',
        ];
    }
}

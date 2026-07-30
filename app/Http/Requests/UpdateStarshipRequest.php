<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStarshipRequest extends FormRequest
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
        return [
            'swapi_id' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                Rule::unique('starships', 'swapi_id')->ignore($this->route('starship')),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'max_atmosphering_speed' => ['sometimes', 'required', 'integer', 'min:0'],
            'cargo_capacity' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStarshipRequest extends FormRequest
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
            'swapi_id' => ['nullable', 'integer', 'min:1', 'unique:starships,swapi_id'],
            'name' => ['required', 'string', 'max:120'],
            'max_atmosphering_speed' => ['required', 'integer', 'min:0'],
            'cargo_capacity' => ['required', 'integer', 'min:0'],
        ];
    }
}

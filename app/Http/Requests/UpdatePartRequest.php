<?php

namespace App\Http\Requests;

use App\Enums\PartCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'part_number' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('parts', 'part_number')->ignore($this->route('part'))],
            'category' => ['sometimes', 'required', Rule::enum(PartCategory::class)],
            'manufacturer' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}

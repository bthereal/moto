<?php

namespace App\Http\Requests;

use App\Enums\PartCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'part_number' => ['required', 'string', 'max:255', 'unique:parts,part_number'],
            'category' => ['required', Rule::enum(PartCategory::class)],
            'manufacturer' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}

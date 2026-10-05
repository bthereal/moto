<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierPartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('supplierPart'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'part_id' => [
                'sometimes',
                'required',
                'exists:parts,id',
                Rule::unique('supplier_parts')
                    ->where('supplier_id', $this->route('supplier')->id)
                    ->ignore($this->route('supplierPart')),
            ],
            'quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'delivery_cost' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}

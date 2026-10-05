<?php

namespace App\Http\Requests;

use App\Enums\VehiclePartStatus;
use App\Models\SupplierPart;
use App\Models\VehiclePart;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class OrderVehiclePartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('order', $this->route('vehiclePart'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supplier_part_id' => ['required', 'exists:supplier_parts,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var VehiclePart $vehiclePart */
            $vehiclePart = $this->route('vehiclePart');

            if ($vehiclePart->status !== VehiclePartStatus::Required) {
                $validator->errors()->add('supplier_part_id', 'Only a required part can be ordered.');

                return;
            }

            $supplierPart = SupplierPart::find($this->input('supplier_part_id'));

            if (! $supplierPart) {
                return;
            }

            if ($supplierPart->part_id !== $vehiclePart->part_id) {
                $validator->errors()->add('supplier_part_id', 'That listing does not stock the required part.');

                return;
            }

            if (! $supplierPart->inStock()) {
                $validator->errors()->add('supplier_part_id', 'That supplier has no stock of this part.');
            }
        });
    }
}

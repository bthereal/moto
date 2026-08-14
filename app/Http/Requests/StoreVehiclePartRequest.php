<?php

namespace App\Http\Requests;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehiclePartRequest extends FormRequest
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
            'part_id' => ['required', 'exists:parts,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Vehicle $vehicle */
            $vehicle = $this->route('vehicle');

            if ($vehicle->status !== VehicleStatus::Testing) {
                $validator->errors()->add(
                    'part_id',
                    'Parts can only be required while the vehicle is in testing.'
                );
            }
        });
    }
}

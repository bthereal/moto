<?php

namespace App\Http\Requests;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
            'team_id' => ['sometimes', 'required', 'exists:teams,id'],
            'driver_id' => ['nullable', 'exists:users,id'],
            'chassis' => ['sometimes', 'required', 'string', 'max:255'],
            'engine_supplier' => ['sometimes', 'required', 'string', 'max:255'],
            'car_number' => ['sometimes', 'required', 'integer', 'min:1', 'max:99'],
            'season_year' => ['sometimes', 'required', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'status' => ['sometimes', Rule::enum(VehicleStatus::class)],
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

            if ($this->input('status') === VehicleStatus::Active->value
                && $vehicle->status === VehicleStatus::Testing
                && $vehicle->hasOutstandingParts()) {
                $validator->errors()->add(
                    'status',
                    'This vehicle cannot be activated until all required parts are fitted.'
                );
            }
        });
    }
}

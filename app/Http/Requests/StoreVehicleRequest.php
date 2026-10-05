<?php

namespace App\Http\Requests;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Vehicle::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'team_id' => ['required', 'exists:teams,id'],
            'driver_id' => ['nullable', 'exists:users,id'],
            'chassis' => ['required', 'string', 'max:255'],
            'engine_supplier' => ['required', 'string', 'max:255'],
            'car_number' => ['required', 'integer', 'min:1', 'max:99'],
            'season_year' => ['required', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'status' => ['sometimes', Rule::enum(VehicleStatus::class)],
        ];
    }
}

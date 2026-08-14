<?php

use App\Enums\VehicleStatus;
use App\Models\Team;
use App\Models\Vehicle;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function deleteVehicle(Vehicle $vehicle): void
    {
        $vehicle->delete();
    }

    public function with(): array
    {
        return [
            'vehicles' => Vehicle::query()
                ->with(['team', 'driver'])
                ->when($this->status, fn ($query) => $query->where('status', $this->status))
                ->orderBy('season_year', 'desc')
                ->orderBy('car_number')
                ->paginate(10),
            'statuses' => VehicleStatus::cases(),
        ];
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Vehicles') }}</h2>
                <select wire:model.live="status" class="rounded-md border-gray-300 text-sm">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach ($statuses as $case)
                        <option value="{{ $case->value }}">{{ ucfirst($case->value) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="pb-2">{{ __('#') }}</th>
                            <th class="pb-2">{{ __('Chassis') }}</th>
                            <th class="pb-2">{{ __('Team') }}</th>
                            <th class="pb-2">{{ __('Driver') }}</th>
                            <th class="pb-2">{{ __('Engine') }}</th>
                            <th class="pb-2">{{ __('Status') }}</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($vehicles as $vehicle)
                            <tr wire:key="vehicle-{{ $vehicle->id }}">
                                <td class="py-3 font-medium text-gray-900">{{ $vehicle->car_number }}</td>
                                <td class="py-3">
                                    <a href="{{ route('vehicles.show', $vehicle) }}" wire:navigate class="hover:underline">
                                        {{ $vehicle->chassis }}
                                    </a>
                                </td>
                                <td class="py-3">
                                    <a href="{{ route('teams.show', $vehicle->team) }}" wire:navigate class="hover:underline">
                                        {{ $vehicle->team->name }}
                                    </a>
                                </td>
                                <td class="py-3 text-gray-500">{{ $vehicle->driver?->name ?? __('Unassigned') }}</td>
                                <td class="py-3 text-gray-500">{{ $vehicle->engine_supplier }}</td>
                                <td class="py-3 text-gray-500 uppercase text-xs tracking-wide">{{ $vehicle->status->value }}</td>
                                <td class="py-3 text-right">
                                    <button
                                        wire:click="deleteVehicle({{ $vehicle->id }})"
                                        wire:confirm="{{ __('Delete this vehicle?') }}"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        {{ __('Delete') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-6">
                    {{ $vehicles->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

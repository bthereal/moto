<?php

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'vehicles' => Vehicle::where('status', VehicleStatus::Testing)
                ->with(['team', 'driver', 'vehicleParts.part'])
                ->orderBy('car_number')
                ->get(),
        ];
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Vehicles in testing') }}</h3>

                @if ($vehicles->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No vehicles are currently in testing.') }}</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-gray-500">
                                <th class="pb-2 w-8"></th>
                                <th class="pb-2">{{ __('#') }}</th>
                                <th class="pb-2">{{ __('Chassis') }}</th>
                                <th class="pb-2">{{ __('Team') }}</th>
                                <th class="pb-2">{{ __('Driver') }}</th>
                                <th class="pb-2">{{ __('Parts') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" x-data="{ open: {} }">
                            @foreach ($vehicles as $vehicle)
                                @php
                                    $totalParts = $vehicle->vehicleParts->count();
                                    $fittedParts = $vehicle->vehicleParts->where('status', \App\Enums\VehiclePartStatus::Fitted)->count();
                                @endphp
                                <tr
                                    wire:key="dash-vehicle-{{ $vehicle->id }}"
                                    @click="open[{{ $vehicle->id }}] = ! open[{{ $vehicle->id }}]"
                                    class="cursor-pointer hover:bg-gray-50"
                                >
                                    <td class="py-3 pl-1">
                                        <svg
                                            class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                            :class="open[{{ $vehicle->id }}] ? 'rotate-90' : ''"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </td>
                                    <td class="py-3 font-medium text-gray-900">{{ $vehicle->car_number }}</td>
                                    <td class="py-3">
                                        <a href="{{ route('vehicles.show', $vehicle) }}" wire:navigate class="hover:underline" @click.stop>
                                            {{ $vehicle->chassis }}
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <a href="{{ route('teams.show', $vehicle->team) }}" wire:navigate class="hover:underline" @click.stop>
                                            {{ $vehicle->team->name }}
                                        </a>
                                    </td>
                                    <td class="py-3 text-gray-500">{{ $vehicle->driver?->name ?? __('Unassigned') }}</td>
                                    <td class="py-3 text-gray-500">
                                        @if ($totalParts > 0)
                                            {{ $fittedParts }}/{{ $totalParts }} {{ __('fitted') }}
                                        @else
                                            <span class="text-gray-400">{{ __('None required') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="p-0 border-0">
                                        <div
                                            class="grid transition-all duration-300 ease-in-out"
                                            :class="open[{{ $vehicle->id }}] ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                                        >
                                            <div class="overflow-hidden">
                                                <div class="bg-gray-50 px-6 py-4">
                                                    @forelse ($vehicle->vehicleParts as $vehiclePart)
                                                        <div class="flex items-center justify-between py-2 {{ ! $loop->last ? 'border-b border-gray-200' : '' }}">
                                                            <div>
                                                                <p class="font-medium text-gray-900 text-sm">{{ $vehiclePart->part->name }}</p>
                                                                <p class="text-xs text-gray-500">{{ $vehiclePart->part->part_number }} &middot; {{ $vehiclePart->part->manufacturer }}</p>
                                                            </div>
                                                            <span @class([
                                                                'text-xs uppercase tracking-wide px-2 py-1 rounded-full',
                                                                'bg-amber-100 text-amber-800' => $vehiclePart->status === \App\Enums\VehiclePartStatus::Required,
                                                                'bg-purple-100 text-purple-800' => $vehiclePart->status === \App\Enums\VehiclePartStatus::Ordered,
                                                                'bg-blue-100 text-blue-800' => $vehiclePart->status === \App\Enums\VehiclePartStatus::InTransit,
                                                                'bg-indigo-100 text-indigo-800' => $vehiclePart->status === \App\Enums\VehiclePartStatus::Delivered,
                                                                'bg-green-100 text-green-800' => $vehiclePart->status === \App\Enums\VehiclePartStatus::Fitted,
                                                            ])>
                                                                {{ $vehiclePart->status->value }}
                                                            </span>
                                                        </div>
                                                    @empty
                                                        <p class="text-sm text-gray-500 py-2">{{ __('No parts required.') }}</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

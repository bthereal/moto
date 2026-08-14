<?php

use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function with(): array
    {
        return [
            'staff' => $this->team->users()->orderBy('role')->get(),
            'vehicles' => $this->team->vehicles()->with('driver')->orderBy('car_number')->get(),
        ];
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center gap-3">
                <span class="inline-block w-4 h-4 rounded-full" style="background-color: {{ $team->color }}"></span>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $team->name }}</h2>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">{{ __('Full name') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ $team->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Base') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ $team->base }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Team principal') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ $team->principal }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Founded') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ $team->founded_year }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Vehicles') }}</h3>
                <div class="divide-y divide-gray-100">
                    @forelse ($vehicles as $vehicle)
                        <div class="py-3 flex items-center justify-between" wire:key="vehicle-{{ $vehicle->id }}">
                            <div>
                                <a href="{{ route('vehicles.show', $vehicle) }}" wire:navigate class="font-medium text-gray-900 hover:underline">
                                    #{{ $vehicle->car_number }} &middot; {{ $vehicle->chassis }}
                                </a>
                                <p class="text-sm text-gray-500">{{ $vehicle->engine_supplier }} &middot; {{ $vehicle->driver?->name ?? __('No driver assigned') }}</p>
                            </div>
                            <span class="text-xs uppercase tracking-wide text-gray-500">{{ $vehicle->status->value }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('No vehicles entered yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Staff & drivers') }}</h3>
                <div class="divide-y divide-gray-100">
                    @forelse ($staff as $person)
                        <div class="py-3 flex items-center justify-between" wire:key="staff-{{ $person->id }}">
                            <div>
                                <p class="font-medium text-gray-900">{{ $person->name }}</p>
                                <p class="text-sm text-gray-500">{{ $person->email }}</p>
                            </div>
                            <span class="text-xs uppercase tracking-wide text-gray-500">{{ $person->role->value }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('No staff assigned yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

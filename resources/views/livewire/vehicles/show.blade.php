<?php

use App\Enums\VehiclePartStatus;
use App\Enums\VehicleStatus;
use App\Models\Part;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Vehicle $vehicle;

    public string $status = '';

    public bool $showAddPartForm = false;

    public ?int $partId = null;

    public function mount(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle->load(['team', 'driver']);
        $this->status = $vehicle->status->value;
    }

    public function updateStatus(): void
    {
        $this->validate(['status' => 'required|string|in:active,testing,retired']);

        if ($this->status === VehicleStatus::Active->value
            && $this->vehicle->status === VehicleStatus::Testing
            && ! $this->vehicle->canActivate()) {
            $this->addError('status', __('This vehicle cannot be activated until all required parts are fitted.'));

            return;
        }

        $this->vehicle->update(['status' => $this->status]);
        $this->vehicle->refresh();
    }

    public function requirePart(): void
    {
        $this->validate(['partId' => 'required|exists:parts,id']);

        if ($this->vehicle->status !== VehicleStatus::Testing) {
            $this->addError('partId', __('Parts can only be required while the vehicle is in testing.'));

            return;
        }

        $this->vehicle->vehicleParts()->create([
            'part_id' => $this->partId,
            'status' => VehiclePartStatus::Required,
        ]);

        $this->reset(['partId', 'showAddPartForm']);
    }

    public function advanceStatus(VehiclePart $vehiclePart): void
    {
        abort_unless($vehiclePart->vehicle_id === $this->vehicle->id, 404);

        $next = match ($vehiclePart->status) {
            VehiclePartStatus::Required => VehiclePartStatus::InTransit,
            VehiclePartStatus::InTransit => VehiclePartStatus::Delivered,
            VehiclePartStatus::Delivered => VehiclePartStatus::Fitted,
            VehiclePartStatus::Fitted => null,
        };

        if ($next !== null) {
            $vehiclePart->update(['status' => $next]);
        }
    }

    public function removePart(VehiclePart $vehiclePart): void
    {
        abort_unless($vehiclePart->vehicle_id === $this->vehicle->id, 404);

        $vehiclePart->delete();
    }

    public function with(): array
    {
        $blockActiveOption = $this->vehicle->status === VehicleStatus::Testing && ! $this->vehicle->canActivate();

        return [
            'statuses' => VehicleStatus::cases(),
            'blockActiveOption' => $blockActiveOption,
            'vehicleParts' => $this->vehicle->vehicleParts()->with('part')->latest()->get(),
            'availableParts' => Part::orderBy('category')->orderBy('name')->get(),
        ];
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                #{{ $vehicle->car_number }} &middot; {{ $vehicle->chassis }}
            </h2>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">{{ __('Team') }}</dt>
                        <dd class="text-gray-900 font-medium">
                            <a href="{{ route('teams.show', $vehicle->team) }}" wire:navigate class="hover:underline">
                                {{ $vehicle->team->name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Driver') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ $vehicle->driver?->name ?? __('Unassigned') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Engine supplier') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ $vehicle->engine_supplier }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Season') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ $vehicle->season_year }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Status') }}</h3>
                <form wire:submit="updateStatus" class="flex items-center gap-3">
                    <select wire:model="status" class="rounded-md border-gray-300 text-sm">
                        @foreach ($statuses as $case)
                            @if ($case !== \App\Enums\VehicleStatus::Active || ! $blockActiveOption)
                                <option value="{{ $case->value }}">{{ ucfirst($case->value) }}</option>
                            @endif
                        @endforeach
                    </select>
                    <x-primary-button type="submit">{{ __('Update') }}</x-primary-button>
                    @if ($blockActiveOption)
                        <span class="text-sm text-amber-600">{{ __('Blocked: parts still outstanding.') }}</span>
                    @endif
                </form>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Parts') }}</h3>
                    @if ($vehicle->status === \App\Enums\VehicleStatus::Testing)
                        <x-secondary-button wire:click="$toggle('showAddPartForm')">
                            {{ __('Require a part') }}
                        </x-secondary-button>
                    @else
                        <span
                            title="{{ __('Move this vehicle to testing to require parts.') }}"
                            class="text-sm text-gray-400 cursor-not-allowed select-none"
                        >
                            {{ __('Require a part') }}
                        </span>
                    @endif
                </div>

                @if ($showAddPartForm)
                    <form wire:submit="requirePart" class="flex items-center gap-3 mb-6 border-b pb-6">
                        <select wire:model="partId" class="rounded-md border-gray-300 text-sm flex-1">
                            <option value="">{{ __('Select a part…') }}</option>
                            @foreach ($availableParts as $part)
                                <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->part_number }})</option>
                            @endforeach
                        </select>
                        <x-primary-button type="submit">{{ __('Require') }}</x-primary-button>
                    </form>
                    <x-input-error :messages="$errors->get('partId')" class="-mt-4 mb-4" />
                @endif

                <div class="divide-y divide-gray-100">
                    @forelse ($vehicleParts as $vehiclePart)
                        <div class="py-3 flex items-center justify-between" wire:key="vp-{{ $vehiclePart->id }}">
                            <div>
                                <p class="font-medium text-gray-900">{{ $vehiclePart->part->name }}</p>
                                <p class="text-sm text-gray-500">{{ $vehiclePart->part->part_number }} &middot; {{ $vehiclePart->part->manufacturer }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span @class([
                                    'text-xs uppercase tracking-wide px-2 py-1 rounded-full',
                                    'bg-amber-100 text-amber-800' => $vehiclePart->status === \App\Enums\VehiclePartStatus::Required,
                                    'bg-blue-100 text-blue-800' => $vehiclePart->status === \App\Enums\VehiclePartStatus::InTransit,
                                    'bg-indigo-100 text-indigo-800' => $vehiclePart->status === \App\Enums\VehiclePartStatus::Delivered,
                                    'bg-green-100 text-green-800' => $vehiclePart->status === \App\Enums\VehiclePartStatus::Fitted,
                                ])>
                                    {{ $vehiclePart->status->value }}
                                </span>
                                @if ($vehiclePart->status !== \App\Enums\VehiclePartStatus::Fitted)
                                    <button
                                        wire:click="advanceStatus({{ $vehiclePart->id }})"
                                        class="text-sm text-indigo-600 hover:text-indigo-800"
                                    >
                                        {{ __('Advance') }}
                                    </button>
                                @endif
                                <button
                                    wire:click="removePart({{ $vehiclePart->id }})"
                                    wire:confirm="{{ __('Remove this part requirement?') }}"
                                    class="text-sm text-red-600 hover:text-red-800"
                                >
                                    {{ __('Remove') }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('No parts required.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

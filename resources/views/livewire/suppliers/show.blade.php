<?php

use App\Models\Part;
use App\Models\Supplier;
use App\Models\SupplierPart;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Supplier $supplier;

    public bool $showAddForm = false;

    public ?int $partId = null;

    public string $quantity = '';

    public string $price = '';

    public string $location = '';

    public string $delivery_cost = '';

    public ?int $editingSupplierPartId = null;

    public function mount(Supplier $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function addSupplierPart(): void
    {
        $this->validate([
            'partId' => ['required', 'exists:parts,id', "unique:supplier_parts,part_id,NULL,id,supplier_id,{$this->supplier->id}"],
            'quantity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'location' => ['required', 'string', 'max:255'],
            'delivery_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $this->supplier->supplierParts()->create([
            'part_id' => $this->partId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'location' => $this->location,
            'delivery_cost' => $this->delivery_cost,
        ]);

        $this->reset(['partId', 'quantity', 'price', 'location', 'delivery_cost', 'showAddForm']);
    }

    public function editSupplierPart(SupplierPart $supplierPart): void
    {
        abort_unless($supplierPart->supplier_id === $this->supplier->id, 404);

        $this->editingSupplierPartId = $supplierPart->id;
        $this->quantity = (string) $supplierPart->quantity;
        $this->price = (string) $supplierPart->price;
        $this->location = $supplierPart->location;
        $this->delivery_cost = (string) $supplierPart->delivery_cost;
    }

    public function updateSupplierPart(SupplierPart $supplierPart): void
    {
        abort_unless($supplierPart->supplier_id === $this->supplier->id, 404);

        $this->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'location' => ['required', 'string', 'max:255'],
            'delivery_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $supplierPart->update([
            'quantity' => $this->quantity,
            'price' => $this->price,
            'location' => $this->location,
            'delivery_cost' => $this->delivery_cost,
        ]);

        $this->reset(['editingSupplierPartId', 'quantity', 'price', 'location', 'delivery_cost']);
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingSupplierPartId', 'quantity', 'price', 'location', 'delivery_cost']);
    }

    public function removeSupplierPart(SupplierPart $supplierPart): void
    {
        abort_unless($supplierPart->supplier_id === $this->supplier->id, 404);

        $supplierPart->delete();
    }

    public function with(): array
    {
        return [
            'supplierParts' => $this->supplier->supplierParts()->with('part')->get(),
            'availableParts' => Part::whereDoesntHave('supplierParts', fn ($query) => $query->where('supplier_id', $this->supplier->id))
                ->orderBy('name')
                ->get(),
        ];
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $supplier->name }}</h2>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">{{ __('Contact email') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ $supplier->contact_email }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Parts stocked') }}</h3>
                    <x-secondary-button wire:click="$toggle('showAddForm')">
                        {{ __('Add part') }}
                    </x-secondary-button>
                </div>

                @if ($showAddForm)
                    <form wire:submit="addSupplierPart" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 border-b pb-6">
                        <div class="sm:col-span-2">
                            <x-input-label for="partId" :value="__('Part')" />
                            <select wire:model="partId" id="partId" class="block mt-1 w-full rounded-md border-gray-300 text-sm">
                                <option value="">{{ __('Select a part…') }}</option>
                                @foreach ($availableParts as $part)
                                    <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->part_number }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('partId')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="quantity" :value="__('Quantity in stock')" />
                            <x-text-input wire:model="quantity" id="quantity" type="number" min="0" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="price" :value="__('Price')" />
                            <x-text-input wire:model="price" id="price" type="number" step="0.01" min="0" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="location" :value="__('Location')" />
                            <x-text-input wire:model="location" id="location" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="delivery_cost" :value="__('Delivery cost')" />
                            <x-text-input wire:model="delivery_cost" id="delivery_cost" type="number" step="0.01" min="0" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('delivery_cost')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2 flex justify-end gap-3">
                            <x-secondary-button type="button" wire:click="$set('showAddForm', false)">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button type="submit">
                                {{ __('Add') }}
                            </x-primary-button>
                        </div>
                    </form>
                @endif

                <div class="divide-y divide-gray-100">
                    @forelse ($supplierParts as $supplierPart)
                        <div class="py-3" wire:key="sp-{{ $supplierPart->id }}">
                            @if ($editingSupplierPartId === $supplierPart->id)
                                <form wire:submit="updateSupplierPart({{ $supplierPart->id }})" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
                                    <div>
                                        <x-input-label :value="__('Quantity')" class="text-xs" />
                                        <x-text-input wire:model="quantity" type="number" min="0" class="block mt-1 w-full text-sm" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Price')" class="text-xs" />
                                        <x-text-input wire:model="price" type="number" step="0.01" min="0" class="block mt-1 w-full text-sm" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Location')" class="text-xs" />
                                        <x-text-input wire:model="location" class="block mt-1 w-full text-sm" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Delivery cost')" class="text-xs" />
                                        <x-text-input wire:model="delivery_cost" type="number" step="0.01" min="0" class="block mt-1 w-full text-sm" />
                                    </div>
                                    <div class="col-span-2 sm:col-span-4 flex justify-end gap-3">
                                        <x-secondary-button type="button" wire:click="cancelEdit">{{ __('Cancel') }}</x-secondary-button>
                                        <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                                    </div>
                                </form>
                            @else
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $supplierPart->part->name }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $supplierPart->part->part_number }} &middot;
                                            {{ $supplierPart->quantity }} {{ __('in stock') }} &middot;
                                            &pound;{{ number_format($supplierPart->price, 2) }} &middot;
                                            {{ $supplierPart->location }} &middot;
                                            {{ __('delivery') }} &pound;{{ number_format($supplierPart->delivery_cost, 2) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm">
                                        <button wire:click="editSupplierPart({{ $supplierPart->id }})" class="text-indigo-600 hover:text-indigo-800">
                                            {{ __('Edit') }}
                                        </button>
                                        <button
                                            wire:click="removeSupplierPart({{ $supplierPart->id }})"
                                            wire:confirm="{{ __('Remove this part from the supplier’s list?') }}"
                                            class="text-red-600 hover:text-red-800"
                                        >
                                            {{ __('Remove') }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 py-2">{{ __('No parts listed yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<?php

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public bool $showCreateForm = false;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $contact_email = '';

    public function createSupplier(): void
    {
        Gate::authorize('create', Supplier::class);

        $this->validate();

        Supplier::create([
            'name' => $this->name,
            'contact_email' => $this->contact_email,
        ]);

        $this->reset(['name', 'contact_email', 'showCreateForm']);
    }

    public function deleteSupplier(Supplier $supplier): void
    {
        Gate::authorize('delete', $supplier);

        $supplier->delete();
    }

    public function with(): array
    {
        return [
            'suppliers' => Supplier::withCount('supplierParts')->orderBy('name')->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Suppliers') }}</h2>
                    @can('create', App\Models\Supplier::class)
                    <x-primary-button wire:click="$toggle('showCreateForm')">
                        {{ __('New supplier') }}
                    </x-primary-button>
                    @endcan
                </div>

                @if ($showCreateForm)
                    <form wire:submit="createSupplier" class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 border-t pt-6">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="contact_email" :value="__('Contact email')" />
                            <x-text-input wire:model="contact_email" id="contact_email" type="email" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2 flex justify-end gap-3">
                            <x-secondary-button type="button" wire:click="$set('showCreateForm', false)">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button type="submit">
                                {{ __('Create supplier') }}
                            </x-primary-button>
                        </div>
                    </form>
                @endif

                <div class="mt-6 divide-y divide-gray-100">
                    @foreach ($suppliers as $supplier)
                        <div class="flex items-center justify-between py-4" wire:key="supplier-{{ $supplier->id }}">
                            <div>
                                <a href="{{ route('suppliers.show', $supplier) }}" wire:navigate class="font-medium text-gray-900 hover:underline">
                                    {{ $supplier->name }}
                                </a>
                                <p class="text-sm text-gray-500">{{ $supplier->contact_email }}</p>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span>{{ $supplier->supplier_parts_count }} {{ __('parts listed') }}</span>
                                @can('delete', $supplier)
                                <button
                                    wire:click="deleteSupplier({{ $supplier->id }})"
                                    wire:confirm="{{ __('Delete this supplier?') }}"
                                    class="text-red-600 hover:text-red-800 text-sm"
                                >
                                    {{ __('Delete') }}
                                </button>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $suppliers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

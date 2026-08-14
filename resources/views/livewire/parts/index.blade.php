<?php

use App\Enums\PartCategory;
use App\Models\Part;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public bool $showCreateForm = false;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:255')]
    public string $part_number = '';

    #[Validate('required|string')]
    public string $category = '';

    #[Validate('required|string|max:255')]
    public string $manufacturer = '';

    #[Validate('nullable|string')]
    public string $description = '';

    public function createPart(): void
    {
        $this->validate();

        Part::create([
            'name' => $this->name,
            'part_number' => $this->part_number,
            'category' => $this->category,
            'manufacturer' => $this->manufacturer,
            'description' => $this->description ?: null,
        ]);

        $this->reset(['name', 'part_number', 'category', 'manufacturer', 'description', 'showCreateForm']);
    }

    public function deletePart(Part $part): void
    {
        $part->delete();
    }

    public function with(): array
    {
        return [
            'parts' => Part::orderBy('category')->orderBy('name')->paginate(15),
            'categories' => PartCategory::cases(),
        ];
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Parts catalog') }}</h2>
                    <x-primary-button wire:click="$toggle('showCreateForm')">
                        {{ __('New part') }}
                    </x-primary-button>
                </div>

                @if ($showCreateForm)
                    <form wire:submit="createPart" class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 border-t pt-6">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="part_number" :value="__('Part number')" />
                            <x-text-input wire:model="part_number" id="part_number" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('part_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="category" :value="__('Category')" />
                            <select wire:model="category" id="category" class="block mt-1 w-full rounded-md border-gray-300 text-sm">
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($categories as $case)
                                    <option value="{{ $case->value }}">{{ ucfirst($case->value) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="manufacturer" :value="__('Manufacturer')" />
                            <x-text-input wire:model="manufacturer" id="manufacturer" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('manufacturer')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea wire:model="description" id="description" rows="2" class="block mt-1 w-full rounded-md border-gray-300 text-sm"></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2 flex justify-end gap-3">
                            <x-secondary-button type="button" wire:click="$set('showCreateForm', false)">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button type="submit">
                                {{ __('Create part') }}
                            </x-primary-button>
                        </div>
                    </form>
                @endif

                <div class="mt-6 divide-y divide-gray-100">
                    @foreach ($parts as $part)
                        <div class="flex items-center justify-between py-4" wire:key="part-{{ $part->id }}">
                            <div>
                                <p class="font-medium text-gray-900">{{ $part->name }}</p>
                                <p class="text-sm text-gray-500">{{ $part->part_number }} &middot; {{ $part->manufacturer }}</p>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span class="text-xs uppercase tracking-wide bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                                    {{ $part->category->value }}
                                </span>
                                <button
                                    wire:click="deletePart({{ $part->id }})"
                                    wire:confirm="{{ __('Delete this part?') }}"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $parts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

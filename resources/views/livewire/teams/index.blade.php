<?php

use App\Models\Team;
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

    #[Validate('required|string|max:255')]
    public string $full_name = '';

    #[Validate('required|string|max:255')]
    public string $base = '';

    #[Validate('required|string|max:255')]
    public string $principal = '';

    #[Validate('required|integer|min:1900')]
    public ?int $founded_year = null;

    #[Validate('required|string|regex:/^#[0-9A-Fa-f]{6}$/')]
    public string $color = '#1e41ff';

    public function createTeam(): void
    {
        Gate::authorize('create', Team::class);

        $this->validate();

        Team::create([
            'name' => $this->name,
            'full_name' => $this->full_name,
            'base' => $this->base,
            'principal' => $this->principal,
            'founded_year' => $this->founded_year,
            'color' => $this->color,
        ]);

        $this->reset(['name', 'full_name', 'base', 'principal', 'founded_year', 'color', 'showCreateForm']);
    }

    public function deleteTeam(Team $team): void
    {
        Gate::authorize('delete', $team);

        $team->delete();
    }

    public function with(): array
    {
        return [
            'teams' => Team::withCount(['users', 'vehicles'])->orderBy('name')->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Teams') }}</h2>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('All teams') }}</h3>
                    @can('create', App\Models\Team::class)
                    <x-primary-button wire:click="$toggle('showCreateForm')">
                        {{ __('New team') }}
                    </x-primary-button>
                    @endcan
                </div>

                @if ($showCreateForm)
                    <form wire:submit="createTeam" class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 border-t pt-6">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="full_name" :value="__('Full entrant name')" />
                            <x-text-input wire:model="full_name" id="full_name" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="base" :value="__('Base')" />
                            <x-text-input wire:model="base" id="base" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('base')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="principal" :value="__('Team principal')" />
                            <x-text-input wire:model="principal" id="principal" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('principal')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="founded_year" :value="__('Founded year')" />
                            <x-text-input wire:model="founded_year" id="founded_year" type="number" class="block mt-1 w-full" />
                            <x-input-error :messages="$errors->get('founded_year')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="color" :value="__('Brand colour')" />
                            <x-text-input wire:model="color" id="color" type="color" class="block mt-1 w-full h-10" />
                            <x-input-error :messages="$errors->get('color')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2 flex justify-end gap-3">
                            <x-secondary-button type="button" wire:click="$set('showCreateForm', false)">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button type="submit">
                                {{ __('Create team') }}
                            </x-primary-button>
                        </div>
                    </form>
                @endif

                <div class="mt-6 divide-y divide-gray-100">
                    @foreach ($teams as $team)
                        <div class="flex items-center justify-between py-4" wire:key="team-{{ $team->id }}">
                            <div class="flex items-center gap-3">
                                <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $team->color }}"></span>
                                <div>
                                    <a href="{{ route('teams.show', $team) }}" wire:navigate class="font-medium text-gray-900 hover:underline">
                                        {{ $team->name }}
                                    </a>
                                    <p class="text-sm text-gray-500">{{ $team->base }} &middot; est. {{ $team->founded_year }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span>{{ $team->users_count }} {{ __('staff') }}</span>
                                <span>{{ $team->vehicles_count }} {{ __('vehicles') }}</span>
                                @can('delete', $team)
                                <button
                                    wire:click="deleteTeam({{ $team->id }})"
                                    wire:confirm="{{ __('Delete this team?') }}"
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
                    {{ $teams->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@extends('layouts.app')

@section('title', 'Teams')

@section('header')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Teams</h1>
        <button type="button" 
                onclick="window.createTeam.show()"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>
            New Team
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Teams List -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <form action="{{ route('teams.index') }}" method="GET" class="mb-6">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search teams..."
                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
            </form>
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($teams as $team)
                    <li class="py-4 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            @if($team->avatar)
                                <img class="h-10 w-10 rounded-full" src="{{ $team->avatar_url }}" alt="">
                            @else
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium">
                                        {{ substr($team->name, 0, 2) }}
                                    </span>
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('teams.show', $team) }}" class="text-lg font-medium text-gray-900 hover:text-indigo-600">
                                    {{ $team->name }}
                                </a>
                                <p class="text-sm text-gray-500">
                                    {{ $team->members_count }} members
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('teams.edit', $team) }}" class="text-indigo-600 hover:text-indigo-900">
                                Edit
                            </a>
                            <form action="{{ route('teams.destroy', $team) }}" 
                                  method="POST" 
                                  class="inline-block"
                                  onsubmit="return confirm('Are you sure you want to delete this team?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="py-8 text-center text-gray-500">
                        No teams found. 
                        <button type="button" 
                                onclick="window.createTeam.show()"
                                class="text-indigo-600 hover:text-indigo-900">
                            Create your first team
                        </button>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<!-- Create Team Modal -->
<div x-data="createTeam"
     x-init="init()"
     x-show="isOpen"
     class="fixed z-50 inset-0 overflow-y-auto"
     x-cloak>
    <!-- Modal content -->
</div>

@push('scripts')
<script>
    window.createTeam = function() {
        return {
            isOpen: false,
            form: {
                name: '',
                description: '',
                avatar: null
            },
            errors: {},
            init() {
                // Initialize any required data
            },
            show() {
                this.isOpen = true;
            },
            close() {
                this.isOpen = false;
                this.form = {
                    name: '',
                    description: '',
                    avatar: null
                };
                this.errors = {};
            },
            async submit() {
                try {
                    const formData = new FormData();
                    formData.append('name', this.form.name);
                    formData.append('description', this.form.description);
                    if (this.form.avatar) {
                        formData.append('avatar', this.form.avatar);
                    }

                    const response = await fetch('{{ route("teams.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.errors = data.errors;
                        return;
                    }

                    window.location.reload();
                } catch (error) {
                    console.error('Error:', error);
                }
            },
            onFileChange(event) {
                this.form.avatar = event.target.files[0];
            }
        }
    }
</script>
@endpush

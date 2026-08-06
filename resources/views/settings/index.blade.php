<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Paramètres de l\'Établissement') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Succès ! </strong>
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}">
                @csrf

                @foreach ($settings as $group => $items)
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg mb-6">
                        <div class="max-w-3xl">
                            <h3 class="text-lg font-medium text-gray-900 capitalize border-b pb-2 mb-4">
                                @switch($group)
                                    @case('general')
                                        🏰 Informations Générales & Coordonnées
                                        @break
                                    @case('pedagogy')
                                        🎓 Horaires & Tenues par Cycle
                                        @break
                                    @case('finance')
                                        💰 Configuration Financière
                                        @break
                                    @default
                                        ⚙️ Groupe {{ ucfirst($group) }}
                                @endswitch
                            </h3>

                            <div class="space-y-4">
                                @foreach ($items as $setting)
                                    <div>
                                        <label for="setting_{{ $setting->key }}" class="block font-medium text-sm text-gray-700">
                                            {{ $setting->description ?? ucfirst(str_replace('_', ' ', $setting->key)) }}
                                        </label>
                                        
                                        @if ($setting->type === 'text')
                                            <textarea id="setting_{{ $setting->key }}" 
                                                      name="settings[{{ $setting->key }}]" 
                                                      rows="3" 
                                                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('settings.'.$setting->key, $setting->value) }}</textarea>
                                        @else
                                            <input type="text" 
                                                   id="setting_{{ $setting->key }}" 
                                                   name="settings[{{ $setting->key }}]" 
                                                   value="{{ old('settings.'.$setting->key, $setting->value) }}" 
                                                   class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <x-primary-button>
                        {{ __('Enregistrer les modifications') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

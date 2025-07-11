<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-sm bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">

                <div class="p-1 text-gray-900">
                    Clients:
                </div>
                <div class="px-2">
                    @foreach($clients as $client)
                        <div class="w-1/2 py-2 px-4 my-2">
                            <a href="/clients/{{ $client->id }}/" class="px-6 hover:border rounded bg-gray-100 hover:bg-blue-100 hover:border-blue-600 text-sm py-2 text-blue-700">
                              {{ $client->name }}
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 text-gray-900">

                </div>

            </div>
        </div>
    </div>
</x-app-layout>

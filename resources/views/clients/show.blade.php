<x-app-layout>


    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto flex items-center justify-between py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Client: {{ $client->name }}
            </h2>

            <div class="flex items-center">
                <div class="flex items-center">
                    &nbsp;
                </div>
            </div>
        </div>
    </header>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-sm bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">

                <div class="p-1 text-gray-900">
                    Campaigns for this client:
                </div>
                <div class="px-2">
                    @foreach($campaigns as $campaign)
                        <div class="w-3/4 py-2 px-4 my-2">
                            <a href="/campaigns/{{ $campaign->id }}/" class="px-6 hover:border rounded bg-gray-100 hover:bg-blue-100 hover:border-blue-600 text-sm py-2 text-blue-700">
                                {{ $campaign->name }}
                            </a>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

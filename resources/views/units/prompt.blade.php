<x-app-layout>

    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto flex items-center justify-between py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="/campaigns/{{ $unit->campaign->id }}/" class="hover:text-gray-600 underline">Campaign {{ $unit->campaign->id }}</a> / Unit {{ $unit->id }}
            </h2>

            <div class="flex items-center">

            </div>
        </div>
    </header>


    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="text-sm text-gray-900">
                    <h2 class="text-lg bold text-gray-900 pb-2">Prompt engineering:</h2>
                    <div class="my-2">

                        <span class="text-sm text-black font-bold">
                            {!! nl2br($unit->generation_prompt_used ?? 'None') !!}
                        </span>
                    </div>

                </div>


        </div>
    </div>
</x-app-layout>

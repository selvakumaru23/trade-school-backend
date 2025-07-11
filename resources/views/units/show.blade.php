<x-app-layout>

    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto flex items-center justify-between py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="/campaigns/{{ $unit->campaign->id }}/" class="mr-4 text-xs bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-600 py-1 px-4 rounded">
                    ← Campaign
                </a>
                Unit {{ $unit->id }}
                <span class="ml-2 text-xs text-gray-600 font-normal">{{ $unit->placement->name ?? 'No placement' }} / {{ $unit->placement->figma_id ?? 'No figma id' }}</span>
            </h2>

            <div class="flex items-center">
                <div class="flex items-center">

                    @if ($previousUnit)
                        <a href="/units/{{ $previousUnit->id }}/" class="text-xs bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-600 py-1 px-4 rounded">
                            ← Previous
                        </a>
                    @endif
                    @if ($nextUnit)
                        <a href="/units/{{ $nextUnit->id }}/" class="ml-2 text-xs bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-600 py-1 px-4 rounded">
                            Next →
                        </a>
                    @endif

                </div>
            </div>
        </div>
    </header>


    <div class="mt-4 py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-3 gap-4">

                <div class="col-span-1 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg
                bg-white
                text-sm text-gray-900">
                    <div class="p-4 border-b border-gray-300 bg-gray-50">
                        <div class="flex justify-between items-center">
                            <h2 class="text-base text-gray-900">Copy</h2>
                            <div>
                                <form action="/admin/genai/resetunitcopy/{{ $unit->id }}" method="POST">
                                    @csrf  {{-- Include the CSRF token --}}
                                    <button type="submit" class="text-xs bg-gray-100 border border-gray-300 hover:border-red-600 hover:bg-red-200 text-gray-600 py-1 px-4 rounded">Re-generate</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="my-2">
                            <livewire:unit-copy :unit_id="$unit->id" />
                        </div>

                    </div>
                </div>
                <div class="col-span-2 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg
                bg-white
                text-sm text-gray-900">
                    <div class="p-4 border-b border-gray-300 bg-gray-50">
                        <div class="flex justify-between items-center">
                            <h2 class="text-base text-gray-900">Images</h2>
                            <div>
                                <form action="/admin/genai/resetunitimages/{{ $unit->id }}" method="POST">
                                    @csrf  {{-- Include the CSRF token --}}
                                    <button type="submit" class="text-xs bg-gray-100 border border-gray-300 hover:border-red-600 hover:bg-red-200 text-gray-600 py-1 px-4 rounded">Re-generate</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="">
                                <livewire:unit-images :unit_id="$unit->id" />
                        </div>
                        <div class="text-gray-500 text-sm py-2">Original(s):</div>
                            @if ($unit->images->isNotEmpty())
                                <div class="flex flex-wrap gap-4">
                                    @foreach ($unit->images as $image)
                                        <a href="{{ $image->url_thumbnail_large }}" target="_blank">
                                            <img src="{{ $image->url_thumbnail_large }}" class="h-24 object-cover">
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p>No images found for this unit.</p>
                            @endif
                        </div>
                </div>

            </div>
            <div class="px-4 mt-6 text-xs text-gray-600">
                <h2 class="text-lg bold text-gray-900 pb-2">Creative direction</h2>
                <h3 class="text-base bold text-gray-900">Ad Unit level</h3>

                <div class="my-2">
                    Copy Direction:<br>
                    {{ $unit->copydirection }}
                </div>
                <div class="my-2">
                    Visual Direction:<br>
                    {{ $unit->visualdirection }}
                </div>
                <div class="my-2">
                    Strategy:<br>
                    {{ $unit->tradeschoolstrategy }}
                </div>

                <h3 class="text-base bold text-gray-900">Campaign level</h3>
                <div class="my-2">
                    Target Audience:<br>
                    {{ $unit->campaign->targetaudience }}
                </div>
                <div class="my-2">
                    Goal:<br>
                    {{ $unit->campaign->goal }}
                </div>
                <div class="my-2">
                    Copy Direction:<br>
                    {{ $unit->campaign->copydirection }}
                </div>
                <div class="my-2">
                    Visual Direction:<br>
                    {{ $unit->campaign->visualdirection }}
                </div>
                <div class="my-2">
                    Strategy:<br>
                    {{ $unit->campaign->tradeschoolstrategy }}
                </div>
                <div class="my-2">
                    Prompt engineering:
                    <a href="/units/{{ $unit->id }}/prompt" class=" text-blue-700 underline ">prompt used</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

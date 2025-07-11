<x-app-layout>


    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto flex items-center justify-between py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Campaign: {{ $campaign->name }}
            </h2>

            <div class="flex items-center">
                <div class="flex items-center">
                    <form action="/admin/genai/resetcampaigncopy/{{ $campaign->id }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs bg-gray-100 border border-gray-300 hover:bg-red-200 hover:border-red-400 text-gray-600 py-1 px-4 rounded">
                            Re-generate Copy</button>
                    </form>
                    <form action="/admin/genai/resetcampaignimages/{{ $campaign->id }}" method="POST">
                        @csrf
                        <button type="submit" class="ml-4 text-xs bg-gray-100 border border-gray-300 hover:bg-red-200 hover:border-red-400 text-gray-600 py-1 px-4 rounded">
                            Re-generate Images</button>
                    </form>
                </div>
            </div>
        </div>
    </header>


    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            <div class="">
                <div class="mt-4 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                                <table class="table-fixed w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        {{--<th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>--}}
                                        <th scope="col" class="w-1/4 px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Unit</th>
                                        <th scope="col" class="w-1/6 px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Images</th>
                                        <th scope="col" class="w-1/8 px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Image Generation</th>
                                        <th scope="col" class="w-1/3 px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Copy Generation</th>
                                        <th scope="col" class="w-1/8 relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach($units as $unit)

                                    <tr>
                                        {{--<td class="whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $unit->name }}</td>--}}
                                        <td class="truncate px-3 py-42 text-sm text-gray-500">{{ $unit->name }}</td>
                                        <td class="truncate px-3 py-2 text-sm text-gray-500">
                                            <div class="flex flex-wrap gap-2">

                                                @foreach($unit->images as $image)
                                                    <img src="{{ $image->url_thumbnail_small }}"  class="h-6 object-cover">
                                                @endforeach

                                            </div>
                                        </td>

                                        <td class="truncate px-3 py-2 text-sm text-gray-500">
                                            <livewire:unit-status-image :unit_id="$unit->id" />
                                        </td>
                                        <td class="truncate px-3 py-2 text-sm text-gray-500">
                                            <livewire:unit-status-copy :unit_id="$unit->id" />
                                        </td>
                                        <td class="truncate py-2 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <a href="/units/{{ $unit->id }}/" class="text-indigo-600 hover:text-indigo-900">Review</a>
                                        </td>
                                    </tr>

                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>



    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-sm bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">

                <div class="p-1 text-gray-900">
                    Audience: {{ $campaign->targetaudience }}
                </div>
                <div class="p-1 text-gray-900">
                    Goal: {{ $campaign->goal }}
                </div>
                <div class="p-1 text-gray-900">
                    Copy direction: {{ $campaign->copydirection }}
                </div>
                <div class="p-1 text-gray-900">
                    Visual direction: {{ $campaign->visualdirection }}
                </div>
                <div class="p-1 text-gray-900">
                    Tradeschool strategy notes: {{ $campaign->tradeschoolstrategy }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

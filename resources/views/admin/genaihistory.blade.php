<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            GenAI history log.
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                {{ $genailogs->links() }}
            </div>

            <div class="space-y-4">
                    @foreach ($genailogs as $genailog)
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-xl font-semibold">{{ $genailog->model }}</h2>
                            <p class="mt-2 text-gray-600">{{ $genailog->prompt }}</p>
                            <p class="mt-2 text-sm text-gray-500">{{ $genailog->created_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $genailogs->links() }}
                </div>


    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-sm font-bold text-gray-900 py-2">Do these in order to get the latest data from Airtable.</div>

                <div class="text-sm font-bold text-gray-900 py-2">First, sync non-unit data.</div>

                <form action="/admin/airtableupdates/allbutunits" method="POST">
                    @csrf  {{-- Include the CSRF token --}}
                    <button type="submit" class="text-xs border border-blue-400 p-2 bg-blue-100 rounded">1. Sync all except units</button>
                </form>
                <div class="text-xs text-gray-700 py-2">Re-syncs all tables except units. Use this to sync clients, campaigns etc.</div>
                <div class="text-sm font-bold text-gray-900 py-2">Next, sync all units and images.</div>

                <form action="/admin/airtableupdates/units" method="POST">
                    @csrf  {{-- Include the CSRF token --}}
                    <button type="submit" class="text-xs border border-blue-400 p-2 bg-blue-100 rounded">2. Sync units</button>
                </form>
                <div class="text-xs text-gray-700 py-2">Re-syncs all units and images, from all campaigns, with Airtable. This step can be much slower if there are many images.</div>
                <div class="text-xs text-gray-700 py-2">
                    <a href="/admin/genaihistory" class="text-blue-600 underline">Generative AI history</a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

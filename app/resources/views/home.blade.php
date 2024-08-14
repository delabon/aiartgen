<x-layout>
    <div class="mt-16 text-center">
        <h1 class="font-bold text-2xl md:text-4xl">Welcome to AiArtGen</h1>

        <p class="text-center mt-4 mb-4">Get inspired by the work of millions of top-rated ai artists around the world.</p>

        <x-link-button href="{{ route('register.create') }}">Get started</x-link-button>
    </div>

    <div class="mt-10">
        <h3 class="font-bold text-xl text-center mb-4">Latest Art</h3>

        @if (count($arts))
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($arts as $art)
                    <x-art :art="$art" />
                @endforeach
            </div>

            <div class="text-center mt-4">
                <x-link-button href="{{ route('arts.index') }}">Browse more inspiration</x-link-button>
            </div>
        @else
            <div>No art at the moment.</div>
        @endif
    </div>
</x-layout>

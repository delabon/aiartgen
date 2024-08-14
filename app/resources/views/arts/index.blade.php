<x-layout>
    <x-page-title>Explore inspiring art</x-page-title>

    @if (count($arts))
        <div class="grid-cols-4 grid gap-4">
            @foreach ($arts as $art)
                <x-art :art="$art" />
            @endforeach
        </div>

        <div class="mt-6">
            {{ $arts->links() }}
        </div>
    @else
        <div>No art at the moment.</div>
    @endif
</x-layout>

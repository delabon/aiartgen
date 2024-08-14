<x-layout>
    <x-page-title>{{ str($user->name)->ucfirst() }}'s art</x-page-title>

    @if (count($arts))
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($arts as $art)
                <div>
                    <a href="{{ route('arts.show', ['art' => $art]) }}">
                        <img src="{{ route('image.show', ['art' => $art]) }}" />
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $arts->links() }}
        </div>
    @else
        <div>No art at the moment.</div>
    @endif
</x-layout>

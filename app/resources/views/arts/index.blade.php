<x-layout>
    <h1 class="font-bold text-4xl text-center my-16">Explore inspiring art</h1>

    @if (count($arts))
        <div class="grid-cols-4 grid gap-4">
            @foreach ($arts as $art)
                <div>
                    <a href="/arts/{{ $art->id }}">
                        <img src="{{ route('image.show', ['art' => $art]) }}">
                    </a>
                    <a href="{{ route('arts.user.art', ['user' => $art->user]) }}">{{ ucwords($art->user->name) }}</a>
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

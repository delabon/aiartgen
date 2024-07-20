<x-layout>
    <h1 class="font-bold text-4xl text-center my-16">{{ str($user->name)->ucfirst() }}'s art</h1>

    @if (count($arts))
        <div class="grid-cols-4 grid gap-4">
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

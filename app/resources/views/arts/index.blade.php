<x-layout>
    <h1 class="font-bold text-4xl text-center my-16">Explore inspiring art</h1>

    <div class="grid-cols-4 grid gap-4">
        @foreach ($arts as $art)
            <div>
                <a href="/arts/{{ $art->id }}">
                    <img src="{{ url('image/') . '/' . $art->id }}">
                </a>
                <a href="/artist/{{ $art->user->id }}">{{ $art->user->name }}</a>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $arts->links() }}
    </div>
</x-layout>

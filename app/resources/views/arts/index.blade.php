<x-layout>
    <h1 class="font-bold text-4xl">Explore inspiring designs</h1>

    <ul>
        @foreach ($arts as $art)
        <li>
            <a href="/arts/{{ $art->id }}">
                <img src="{{ url('image/') . '/' . $art->id }}">
            </a>
        </li>
        @endforeach
    </ul>

    <div>
        {{ $arts->links() }}
    </div>
</x-layout>

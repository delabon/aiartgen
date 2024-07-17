<x-layout>
    <h1 class="font-bold text-4xl text-center my-16">{{ $art->title }}</h1>

    <div class="flex flex-col justify-center">
        <img src="{{ url('image/') . '/' . $art->id }}">

        <div class="mt-4">
            By <a href="/artist/{{ $art->user->id }}" class="hover:underline"><strong>{{ $art->user->name }}</strong></a>
        </div>
    </div>

</x-layout>

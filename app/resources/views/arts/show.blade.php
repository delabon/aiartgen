<x-layout>
    <h1 class="font-bold text-4xl text-center my-16">{{ ucfirst($art->title) }}</h1>

    <div class="flex flex-col justify-center">
        <img src="{{ url('image/') . '/' . $art->id }}">

        <div class="mt-4 flex items-center justify-between">
            <div>
                By <a href="{{ route('arts.user.art', ['user' => $art->user]) }}" class="hover:underline"><strong>{{ ucwords($art->user->name) }}</strong></a>
            </div>

            @can('edit', $art)
                <div class="flex items-center">
                    <a href="{{ route('arts.edit', ['art' => $art]) }}" class="hover:underline">Edit art</a>
                    <span class="mx-1">/</span>
                    <a href="#" class="hover:underline">Delete art</a>
                </div>
            @endcan
        </div>
    </div>

</x-layout>

<x-layout>
    <x-page-title>{{ str($user->name)->ucfirst() }}'s art</x-page-title>

    @if (count($arts))
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($arts as $art)
                <x-art :art="$art" :showAuthor="false" />
            @endforeach
        </div>

        <div class="mt-6">
            {{ $arts->links() }}
        </div>
    @else
        <div>No art at the moment.</div>
    @endif
</x-layout>

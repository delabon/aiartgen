@props([
    'art' => null,
    'showAuthor' => true,
])

<div>
    <a href="{{ route('arts.show', ['art' => $art]) }}">
        <img src="{{ route('image.show', ['art' => $art]) }}">
    </a>

    @if ($showAuthor)
        <a href="{{ route('arts.user.art', ['user' => $art->user]) }}">{{ ucwords($art->user->name) }}</a>
    @endif
</div>

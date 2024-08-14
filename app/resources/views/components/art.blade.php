@props([
    'art'
])

<div>
    <a href="/arts/{{ $art->id }}">
        <img src="{{ route('image.show', ['art' => $art]) }}">
    </a>
    <a href="{{ route('arts.user.art', ['user' => $art->user]) }}">{{ ucwords($art->user->name) }}</a>
</div>

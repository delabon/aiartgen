<x-layout>
    <x-page-title>Edit art</x-page-title>

    <form class="w-full mx-auto" action="{{ route('arts.update', ['art' => $art]) }}" method="post">
        @csrf
        @method('patch')

        <div class="mb-5">
            <x-form-label for="title">Give your art a title</x-form-label>
            <x-form-input type="text" name="title" id="title" placeholder="Dogs are our heroes" :required="true" :value="old('title', $art->title)"/>
            <x-form-error name="title"/>
        </div>

        <div class="mb-5">
            <img src="{{ url('image/') . '/' . $art->id }}" width="256">
        </div>

        <x-form-button>Update</x-form-button>
    </form>

</x-layout>

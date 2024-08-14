<x-layout>
    <x-page-title>Create/Generate art</x-page-title>

    <form class="w-full mx-auto" action="/arts" method="post">
        @csrf

        <div class="mb-5">
            <x-form-label for="prompt">What's on your mind?</x-form-label>
            <x-form-input type="text" name="prompt" id="prompt" placeholder="Make hero dogs" :required="true" :value="old('prompt')"/>
            <x-form-error name="prompt"/>
        </div>

        <div class="mb-5">
            <x-form-label for="title">Give your art a title</x-form-label>
            <x-form-input type="text" name="title" id="title" placeholder="Dogs are our heroes" :required="true" :value="old('title')"/>
            <x-form-error name="title"/>
        </div>

        <x-form-button>Generate</x-form-button>
    </form>

</x-layout>

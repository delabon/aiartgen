<x-layout>
    <h1 class="font-bold text-4xl text-center my-16">Create/Generate art</h1>

    <form class="w-full mx-auto">
        <div class="mb-5">
            <x-form-label for="prompt">What's on your mind?</x-form-label>
            <x-form-input type="text" id="prompt" placeholder="Make hero dogs" :required="true"/>
        </div>

        <div class="mb-5">
            <x-form-label for="title">Give your art a title</x-form-label>
            <x-form-input type="text" id="title" placeholder="Dogs are our heroes" :required="true"/>
        </div>

        <x-form-button>Generate</x-form-button>
    </form>

</x-layout>

<x-layout>
    <h1 class="font-bold text-4xl text-center my-16">Send reset-password email</h1>

    <form class="w-full mx-auto" action="{{ route('password.reset.store') }}" method="post">
        @csrf

        <div class="mb-5">
            <x-form-label for="email">Email</x-form-label>
            <x-form-input type="email" name="email" id="email" placeholder="john@example.com" :required="true" :value="old('email')"/>
            <x-form-error name="email"/>
        </div>

        <x-form-button>Submit</x-form-button>
    </form>

</x-layout>

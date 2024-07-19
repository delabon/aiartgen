<x-layout>
    <h1 class="font-bold text-4xl text-center my-16">Login</h1>

    <form class="w-full mx-auto" action="/login" method="post">
        @csrf

        <div class="mb-5">
            <x-form-label for="email">Email</x-form-label>
            <x-form-input type="email" name="email" id="email" placeholder="john@example.com" :required="true" :value="old('email')"/>
            <x-form-error name="email"/>
        </div>

        <div class="mb-5">
            <x-form-label for="password">Password</x-form-label>
            <x-form-input type="password" name="password" id="password" placeholder="********" :required="true" :value="old('password')"/>
            <x-form-error name="password"/>
        </div>

        <x-form-button>Login</x-form-button>
    </form>

</x-layout>

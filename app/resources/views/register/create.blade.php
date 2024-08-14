<x-layout>
    <x-page-title>Register</x-page-title>

    <form class="w-full mx-auto" action="/register" method="post">
        @csrf

        <div class="mb-5">
            <x-form-label for="name">Full name</x-form-label>
            <x-form-input type="text" name="name" id="name" placeholder="John Doe" :required="true" :value="old('name')"/>
            <x-form-error name="name"/>
        </div>

        <div class="mb-5">
            <x-form-label for="username">Username</x-form-label>
            <x-form-input type="text" name="username" id="username" placeholder="john" :required="true" :value="old('username')"/>
            <x-form-error name="username"/>
        </div>

        <div class="mb-5">
            <x-form-label for="email">Email</x-form-label>
            <x-form-input type="email" name="email" id="email" placeholder="john@example.com" :required="true" :value="old('email')"/>
            <x-form-error name="email"/>
        </div>

        <div class="mb-5">
            <x-form-label for="email_confirmation">Confirm email</x-form-label>
            <x-form-input type="email" name="email_confirmation" id="email_confirmation" placeholder="john@example.com" :required="true" :value="old('email_confirmation')"/>
            <x-form-error name="email_confirmation"/>
        </div>

        <div class="mb-5">
            <x-form-label for="password">Password</x-form-label>
            <x-form-input type="password" name="password" id="password" placeholder="********" :required="true" :value="old('password')"/>
            <x-form-error name="password"/>
        </div>

        <div class="mb-5">
            <x-form-label for="password_confirmation">Confirm password</x-form-label>
            <x-form-input type="password" name="password_confirmation" id="password_confirmation" placeholder="********" :required="true" :value="old('password_confirmation')"/>
            <x-form-error name="password_confirmation"/>
        </div>

        <x-form-button>Register</x-form-button>
    </form>

</x-layout>

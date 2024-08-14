<x-layout>
    <x-page-title>Reset password</x-page-title>

    <form class="w-full mx-auto" action="{{ route('password.reset.update', ['user' => $user]) }}" method="post">
        @csrf
        @method('patch')

        <x-form-input type="hidden" name="reset_password_token" value="{{ $token }}"/>

        <div class="mb-5">
            <x-form-label for="password">Password</x-form-label>
            <x-form-input type="password" name="password" id="password" placeholder="*******" :required="true" :value="old('password')"/>
            <x-form-error name="password"/>
        </div>

        <div class="mb-5">
            <x-form-label for="password_confirmation">Confirm password</x-form-label>
            <x-form-input type="password" name="password_confirmation" id="password_confirmation" placeholder="*******" :required="true" :value="old('password_confirmation')"/>
            <x-form-error name="password_confirmation"/>
        </div>

        <x-form-button>Reset</x-form-button>
    </form>

</x-layout>

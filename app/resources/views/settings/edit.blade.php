<x-layout>
    <x-page-title>Settings</x-page-title>

    <form class="w-full mx-auto" action="{{ route('settings.update.basic') }}" method="post">
        @csrf
        @method('patch')

        <div class="mb-5">
            <x-form-label for="name">Full name</x-form-label>
            <x-form-input type="text" name="name" id="name" placeholder="John Doe" :required="true" :value="old('name', auth()->user()->name)"/>
            <x-form-error name="name"/>
        </div>

        <div class="mb-5">
            <x-form-label for="username">Username</x-form-label>
            <x-form-input type="text" name="username" id="username" placeholder="johndoe" :required="true" :value="old('username', auth()->user()->username)"/>
            <x-form-error name="username"/>
        </div>

        <div class="mb-5">
            <x-form-label for="email">Email</x-form-label>
            <x-form-input type="email" name="email" id="email" placeholder="johndoe@example.com" :required="true" :value="old('email', auth()->user()->email)"/>
            <x-form-error name="email"/>
        </div>

        <x-form-button>Update</x-form-button>
    </form>

    <hr class="my-6">

    <form class="w-full mx-auto" action="{{ route('settings.update.password') }}" method="post">
        @csrf
        @method('patch')

        <div class="mb-5">
            <x-form-label for="old_password">Old password</x-form-label>
            <x-form-input type="password" name="old_password" id="old_password" placeholder="******" :required="true" :value="old('old_password')"/>
            <x-form-error name="old_password"/>
        </div>

        <div class="mb-5">
            <x-form-label for="password">New password</x-form-label>
            <x-form-input type="password" name="password" id="password" placeholder="******" :required="true" :value="old('password')"/>
            <x-form-error name="password"/>
        </div>

        <div class="mb-5">
            <x-form-label for="password_confirmation">Confirm password</x-form-label>
            <x-form-input type="password" name="password_confirmation" id="password_confirmation" placeholder="******" :required="true" :value="old('password_confirmation')"/>
            <x-form-error name="password_confirmation"/>
        </div>

        <x-form-button>Update</x-form-button>
    </form>

    <hr class="my-6">

    <form class="w-full mx-auto" action="{{ route('settings.destroy') }}" method="post">
        @csrf
        @method('delete')

        <x-form-button class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm w-auto px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">Delete account</x-form-button>
    </form>
</x-layout>

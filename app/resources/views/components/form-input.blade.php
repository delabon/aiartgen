@props(['required' => false])

<input {{ $attributes->merge(['class' => 'border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5']) }} {{ $required ? 'required' : '' }} />

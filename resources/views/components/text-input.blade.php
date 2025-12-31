@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-2 border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400/30 rounded-lg shadow-sm transition-all duration-200 hover:border-gray-400']) !!}>

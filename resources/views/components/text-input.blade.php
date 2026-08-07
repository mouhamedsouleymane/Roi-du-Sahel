@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-purple-500 focus:ring-purple-400 rounded-lg shadow-sm py-2.5 px-3.5 text-base min-h-[44px] w-full transition-colors duration-150']) }}>

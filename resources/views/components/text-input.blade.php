@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-purple-300 hover:border-purple-400 focus:border-purple-500 focus:ring-purple-400 rounded-lg shadow-sm py-2.5 px-3.5 text-base min-h-[44px] w-full transition-colors duration-150']) }}>

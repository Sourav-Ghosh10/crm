<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-150 shadow-lg shadow-red-600/25', 'style' => 'background-color: #dc2626;']) }}>
    {{ $slot }}
</button>

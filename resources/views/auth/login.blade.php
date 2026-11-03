<x-guest-layout>
    <div class="mb-10 text-center">
        <h2 class="text-3xl font-extrabold text-white mb-3">Welcome Back</h2>
        <p class="text-slate-400 text-sm leading-relaxed">Please enter your credentials to access the <br> management dashboard.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email Address')" class="text-xs font-semibold text-slate-400 uppercase tracking-wider ms-1" />
            <x-text-input id="email" class="block w-full bg-white/5 border-white/10 text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 rounded-2xl py-3" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <div class="flex items-center justify-between px-1">
                <x-input-label for="password" :value="__('Password')" class="text-xs font-semibold text-slate-400 uppercase tracking-wider" />
                @if (Route::has('password.request'))
                    <a class="text-[11px] font-medium text-indigo-400 hover:text-indigo-300 transition-colors"
                        href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div class="relative" x-data="{ show: false }">
                <x-text-input id="password"
                    class="block w-full bg-white/5 border-white/10 text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 rounded-2xl py-3 pr-10"
                    type="password"
                    ::type="show ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    style="padding-right: 2.75rem;" />

                <button type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-indigo-800 transition-colors focus:outline-none"
                    tabindex="-1"
                    :aria-label="show ? 'Hide password' : 'Show password'"
                    :title="show ? 'Hide password' : 'Show password'">
                    <!-- Eye icon (shown when password is hidden) -->
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <!-- Eye slash icon (shown when password is visible) -->
                    <svg x-show="show" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-start px-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox"
                    class="w-4 h-4 rounded border-white/10 bg-white/5 text-indigo-600 shadow-sm focus:ring-indigo-500/20 focus:ring-offset-0 transition-colors"
                    name="remember">
                <span class="ms-2 text-xs text-slate-400 group-hover:text-slate-300 transition-colors">{{ __('Stay signed in') }}</span>
            </label>
        </div>

        <div class="pt-4">
            <x-primary-button class="w-full justify-center py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-2xl transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98]">
                {{ __('Sign In to Dashboard') }}
            </x-primary-button>
        </div>

        <div class="text-center text-xs text-slate-500 pt-6">
            New to the platform?
            <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-bold transition-colors">Create an account</a>
        </div>
    </form>
</x-guest-layout>
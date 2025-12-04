<x-guest-layout>
    <x-auth-session-status class="mb-4 text-sm font-semibold text-emerald-500" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div class="md-form-field">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="md-form-field">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between mt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border border-slate-400 text-indigo-600 focus:ring-indigo-500" />
                <span>{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="md-link text-sm font-semibold" href="{{ route('password.request') }}">
                    {{ __('Esqueceu sua senha?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
                <x-primary-button>
                    {{ __('Entrar') }}
                </x-primary-button>
        </div>
    </form>
</x-guest-layout>

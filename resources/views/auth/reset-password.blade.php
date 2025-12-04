<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}" class="md-card__form">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="md-form-field">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="md-form-field">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="md-form-field">
            <x-input-label for="password_confirmation" :value="__('Confirmar senha')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="md-card__submit">
            <x-primary-button>
                {{ __('Redefinir senha') }}
            </x-primary-button>
        </div>
    </form>

    <div class="md-card__actions md-card__actions--stacked">
        <span class="md-card__description">Lembrou da senha?</span>
        <a class="md-card__link" href="{{ route('login') }}">{{ __('Voltar ao login') }}</a>
    </div>
</x-guest-layout>

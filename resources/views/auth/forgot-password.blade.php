<x-guest-layout>
    <p class="md-card__description">
        {{ __('Esqueceu sua senha? Sem problemas. Informe seu e-mail e enviaremos um link para redefinir sua senha e seguir acessando sua conta.') }}
    </p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-sm font-semibold text-emerald-500" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="md-card__form">
        @csrf

        <!-- Email Address -->
        <div class="md-form-field">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="md-card__submit">
            <x-primary-button>
                {{ __('Enviar link de redefinição') }}
            </x-primary-button>
        </div>
    </form>

    <div class="md-card__actions md-card__actions--stacked">
        <span class="md-card__description">Já lembra da senha?</span>
        <a class="md-card__link" href="{{ route('login') }}">{{ __('Voltar ao login') }}</a>
    </div>
</x-guest-layout>

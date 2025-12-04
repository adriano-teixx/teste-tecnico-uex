<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Excluir conta') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Depois que sua conta for excluída, todos os recursos e dados serão permanentemente removidos. Antes de excluir sua conta, baixe quaisquer dados ou informações que queira manter.') }}
        </p>

        <p class="mt-2 text-sm font-semibold text-red-600">
            {{ __('Regra: quando você remover sua própria conta, tudo o que foi cadastrado por você será excluído da base de dados.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Excluir conta') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Tem certeza de que deseja excluir sua conta?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Depois que sua conta for excluída, todos os recursos e dados serão permanentemente removidos. Digite sua senha para confirmar que deseja excluir sua conta de forma permanente.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Senha') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Senha') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancelar') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Excluir conta') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>

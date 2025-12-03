<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header">
            <div>
                <p class="dashboard-kicker">Configurações</p>
                <h2>Chave do Google Maps</h2>
            </div>
        </div>
    </x-slot>

    <section class="settings-page">
        <div class="settings-card">
            <h2>Chave do Google Maps</h2>
            <p>Informe a chave da API para que o mapa deste painel e demais integrações possam ser carregados.</p>

            @if (session('status'))
                <p class="settings-status settings-status--success">
                    {{ session('status') }}
                </p>
            @endif

            <form method="POST" action="{{ route('settings.google_maps.update') }}">
                @csrf
                @method('PATCH')

                <input
                    id="google_maps_key"
                    name="google_maps_key"
                    type="text"
                    placeholder="pk.********************************"
                    value="{{ old('google_maps_key', $googleMapsKey) ?? '' }}"
                >

                @error('google_maps_key')
                    <p class="input-error">{{ $message }}</p>
                @enderror

                <button type="submit" class="btn-secondary">
                    Salvar chave
                </button>
            </form>
        </div>
    </section>
</x-app-layout>

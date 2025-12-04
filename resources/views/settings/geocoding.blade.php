<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header">
            <div>
                <p class="dashboard-kicker">Configurações</p>
                <h2>Geocodificação</h2>
            </div>
        </div>
    </x-slot>

    <section class="settings-page">
        @if ($needsOnboarding)
            <div class="settings-onboarding-card">
                <div>
                    <p class="settings-onboarding-card__eyebrow">Primeiros passos</p>
                    <h3>Ajuste as configurações do Google Maps</h3>
                    <p class="settings-onboarding-card__description">
                        Ainda não encontramos uma chave ativa ou uma fonte personalizada de geocodificação. Use os passos abaixo para habilitar o mapa no painel.
                    </p>
                </div>

                <ol class="settings-onboarding-card__list">
                    <li>Crie um projeto no Google Cloud Platform e habilite as APIs do Maps JavaScript e Geocodificação.</li>
                    <li>Gere uma chave de API, aplique restrições recomendadas e copie o valor.</li>
                    <li>Informe a chave neste painel, escolha o provedor desejado e salve.</li>
                </ol>

                <div class="settings-onboarding-card__footer">
                    <p>
                        Não quer depender do Google? Escolha o OpenStreetMap como fonte e continue usando o recurso sem a chave.
                    </p>
                    <a
                        class="settings-onboarding-card__link"
                        href="https://developers.google.com/maps/documentation/javascript/get-api-key"
                        target="_blank"
                        rel="noreferrer noopener"
                    >
                        Saber como gerar uma chave
                    </a>
                </div>
            </div>
        @endif

        <div class="settings-card">
            <p class="settings-card-description">Escolha a fonte das coordenadas e mantenha os endpoints atualizados para o painel.</p>

            @if (session('status'))
                <p class="settings-status settings-status--success">
                    {{ session('status') }}
                </p>
            @endif

            <form method="POST" action="{{ route('settings.google_maps.update') }}">
                @csrf
                @method('PATCH')

                <div class="settings-section">
                    <h3>Fonte de geocodificação</h3>
                    <p>Defina se os endereços devem ser consultados pelo Google Maps ou pelo OpenStreetMap/Nominatim.</p>

                    <label class="settings-radio">
                        <input
                            type="radio"
                            name="geocoding_provider"
                            value="google"
                            @if (old('geocoding_provider', $geocodingProvider) === 'google') checked @endif
                        >
                        <span>Google Maps</span>
                    </label>

                    <label class="settings-radio">
                        <input
                            type="radio"
                            name="geocoding_provider"
                            value="openstreet"
                            @if (old('geocoding_provider', $geocodingProvider) === 'openstreet') checked @endif
                        >
                        <span>OpenStreetMap (via Nominatim)</span>
                    </label>

                    @error('geocoding_provider')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="settings-section">
                    <h3>Chave do Google Maps</h3>
                    <p>Informe a chave para carregar o mapa e realizar o fallback caso o OpenStreetMap não seja suficiente.</p>

                    <input
                        class="settings-input"
                        id="google_maps_key"
                        name="google_maps_key"
                        type="text"
                        placeholder="pk.********************************"
                        value="{{ old('google_maps_key', $googleMapsKey) ?? '' }}"
                    >

                    @error('google_maps_key')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-secondary">
                    Salvar configurações
                </button>
            </form>
        </div>
    </section>
</x-app-layout>

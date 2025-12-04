<x-modal name="contact-registration" focusable maxWidth="2xl">
    <form
        class="p-6 space-y-6"
        @submit.prevent="submitForm()"
        novalidate
    >
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Cadastrar contato') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Preencha os dados do ponto de contato. Todos os campos marcados são necessários para que possamos localizar o endereço.') }}
            </p>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('A plataforma possui um sistema de ajuda para o preenchimento do endereço do contato, onde você pode informar UF, cidade e um trecho do endereço e receber sugestões que já completam os campos automaticamente.') }}
            </p>
        </header>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="contact-name" value="{{ __('Nome completo') }}" />
                <x-text-input
                    id="contact-name"
                    name="name"
                    type="text"
                    x-model="form.name"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Nome completo') }}"
                />
                <p class="md-helper-text" x-show="errors.name" x-text="errors.name ? errors.name[0] : ''"></p>
            </div>

            <div>
                <x-input-label for="contact-cpf" value="{{ __('CPF') }}" />
                <x-text-input
                    id="contact-cpf"
                    name="cpf"
                    type="text"
                    x-model="form.cpf"
                    x-on:input="handleCpfInput($event)"
                    x-on:blur="normalizeCpf()"
                    class="mt-1 block w-full"
                    placeholder="{{ __('000.000.000-00') }}"
                />
                <p class="md-helper-text" x-show="errors.cpf" x-text="errors.cpf ? errors.cpf[0] : ''"></p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="contact-phone" value="{{ __('Telefone') }}" />
                <x-text-input
                    id="contact-phone"
                    name="phone"
                    type="text"
                    x-model="form.phone"
                    x-on:input="handlePhoneInput($event)"
                    x-on:blur="normalizePhone()"
                    class="mt-1 block w-full"
                    placeholder="{{ __('(00) 00000-0000') }}"
                />
                <p class="md-helper-text" x-show="errors.phone" x-text="errors.phone ? errors.phone[0] : ''"></p>
            </div>

            <div>
                <x-input-label for="contact-cep" value="{{ __('CEP') }}" />
                <x-text-input
                    id="contact-cep"
                    name="cep"
                    type="text"
                    x-model="form.cep"
                    x-on:input="handleCepInput($event)"
                    x-on:blur="normalizeCep()"
                    class="mt-1 block w-full"
                    placeholder="{{ __('00000-000') }}"
                />
                <div class="flex items-center gap-2 mt-2">
                    <button
                        type="button"
                        class="text-sm text-indigo-600 hover:text-indigo-800"
                        :disabled="addressLoading"
                        x-on:click="lookupAddress()"
                    >
                        <span x-text="addressLoading ? '{{ __('Buscando...') }}' : '{{ __('Buscar endereço') }}'"></span>
                    </button>
                    <p class="text-xs text-gray-500">Informe cidade, estado e rua para sugestões precisas.</p>
                </div>
                <p
                    class="text-xs text-indigo-600 mt-1"
                    x-text="geocodeLoading ? '{{ __('Obtendo coordenadas...') }}' : ''"
                    x-show="geocodeLoading"
                ></p>
                <p class="md-helper-text" x-show="errors.cep" x-text="errors.cep ? errors.cep[0] : ''"></p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="contact-street" value="{{ __('Rua / Avenida') }}" />
                <x-text-input
                    id="contact-street"
                    name="street"
                    type="text"
                    x-model="form.street"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Rua principal') }}"
                />
                <p class="md-helper-text" x-show="errors.street" x-text="errors.street ? errors.street[0] : ''"></p>
            </div>

            <div>
                <x-input-label for="contact-number" value="{{ __('Número') }}" />
                <x-text-input
                    id="contact-number"
                    name="number"
                    type="text"
                    x-model="form.number"
                    class="mt-1 block w-full"
                    placeholder="{{ __('123') }}"
                />
                <p class="md-helper-text" x-show="errors.number" x-text="errors.number ? errors.number[0] : ''"></p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="contact-complement" value="{{ __('Complemento') }}" />
                <x-text-input
                    id="contact-complement"
                    name="complement"
                    type="text"
                    x-model="form.complement"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Quadra, lote, sala...') }}"
                />
                <p class="md-helper-text" x-show="errors.complement" x-text="errors.complement ? errors.complement[0] : ''"></p>
            </div>

            <div>
                <x-input-label for="contact-district" value="{{ __('Bairro') }}" />
                <x-text-input
                    id="contact-district"
                    name="district"
                    type="text"
                    x-model="form.district"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Centro') }}"
                />
                <p class="md-helper-text" x-show="errors.district" x-text="errors.district ? errors.district[0] : ''"></p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="contact-city" value="{{ __('Cidade') }}" />
                <x-text-input
                    id="contact-city"
                    name="city"
                    type="text"
                    x-model="form.city"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Brasília') }}"
                />
                <p class="md-helper-text" x-show="errors.city" x-text="errors.city ? errors.city[0] : ''"></p>
            </div>

            <div>
                <x-input-label for="contact-state" value="{{ __('Estado (UF)') }}" />
                <x-text-input
                    id="contact-state"
                    name="state"
                    type="text"
                    x-model="form.state"
                    class="mt-1 block w-full uppercase"
                    placeholder="{{ __('DF') }}"
                />
                <p class="md-helper-text" x-show="errors.state" x-text="errors.state ? errors.state[0] : ''"></p>
            </div>
        </div>

        <template x-if="addressSuggestions.length">
            <div class="address-suggestions">
                <p class="text-sm font-semibold text-gray-600">{{ __('Sugestões de endereço') }}</p>
                <div class="mt-2 grid gap-2">
                    <template x-for="(item, index) in addressSuggestions" :key="`${item.cep}-${index}`">
                        <button
                            type="button"
                            class="address-suggestion"
                            x-on:click="selectSuggestion(item)"
                        >
                            <span class="font-medium" x-text="item.street"></span>
                            <small class="text-xs text-gray-500" x-text="`${item.district} · ${item.city}/${item.state}`"></small>
                        </button>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="selectedCoordinates">
            <p class="text-xs text-gray-600">
                {{ __('Coordenadas aproximadas') }}:
                <span class="font-semibold" x-text="`${selectedCoordinates.latitude.toFixed(5)}, ${selectedCoordinates.longitude.toFixed(5)}`"></span>
            </p>
        </template>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancelar') }}
            </x-secondary-button>
            <x-primary-button x-bind:disabled="loading">
                <span x-text="loading ? '{{ __('Salvando...') }}' : '{{ __('Salvar contato') }}'"></span>
            </x-primary-button>
        </div>
    </form>
</x-modal>

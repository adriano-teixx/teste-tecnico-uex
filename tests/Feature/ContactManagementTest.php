<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContactManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_contact_with_coordinates(): void
    {
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'results' => [
                    [
                        'geometry' => [
                            'location' => [
                                'lat' => -23.564,
                                'lng' => -46.648,
                            ],
                        ],
                    ],
                ],
                'status' => 'OK',
            ]),
        ]);

        config(['services.google_maps.key' => 'maps-key']);

        $user = User::factory()->create();

        $payload = [
            'name' => 'Ana Silva',
            'cpf' => '52998224725',
            'phone' => '+5511999999999',
            'cep' => '04534011',
            'street' => 'Rua José Maria Lisboa',
            'number' => '123',
            'complement' => 'Apto 55',
            'district' => 'Jardins',
            'city' => 'São Paulo',
            'state' => 'SP',
        ];

        $response = $this
            ->actingAs($user)
            ->postJson('/contacts', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Ana Silva')
            ->assertJsonPath('data.address.city', 'São Paulo');

        $this->assertDatabaseHas('contacts', [
            'user_id' => $user->id,
            'name' => 'Ana Silva',
            'cpf' => '52998224725',
        ]);
    }

    public function test_user_can_filter_contacts_by_name_or_cpf(): void
    {
        $user = User::factory()->create();

        Contact::factory()->for($user)->create([
            'name' => 'Rafael Gomes',
            'cpf' => '11144477735',
        ]);

        Contact::factory()->for($user)->create([
            'name' => 'Bruno Pereira',
            'cpf' => '22233344455',
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/contacts?search=gomes');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_user_can_delete_contact(): void
    {
        $user = User::factory()->create();

        $contact = Contact::factory()->for($user)->create();

        $response = $this
            ->actingAs($user)
            ->deleteJson("/contacts/{$contact->id}");

        $response->assertNoContent();
        $this->assertNull($contact->fresh());
    }

    public function test_mock_contact_endpoint_creates_contact_when_enabled(): void
    {
        config(['features.mock_contacts' => true]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/contacts/mock');

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name', 'cpf']]);

        $this->assertDatabaseCount('contacts', 1);
        $this->assertEquals(1, $user->fresh()->contacts()->count());
    }

    public function test_mock_contact_endpoint_is_disabled_by_default(): void
    {
        config(['features.mock_contacts' => false]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/contacts/mock');

        $response->assertNotFound();
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_address_search_uses_viacep(): void
    {
        Http::fake([
            'viacep.com.br/ws/*' => Http::response([
                [
                    'cep' => '01001000',
                    'logradouro' => 'Praça da Sé',
                    'bairro' => 'Sé',
                    'localidade' => 'São Paulo',
                    'uf' => 'SP',
                ],
                [
                    'cep' => '01002000',
                    'logradouro' => 'Rua Direita',
                    'bairro' => 'Sé',
                    'localidade' => 'São Paulo',
                    'uf' => 'SP',
                ],
            ]),
        ]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/addresses?uf=SP&city=São Paulo&street=Praça');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }
}

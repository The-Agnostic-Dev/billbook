<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_client_can_be_added(): void
    {
        $response = $this->post(route('clients.store'), [
            'name' => 'Ngozi Eze',
            'company' => 'Eze Bakeries',
            'email' => 'ngozi@ezebakeries.ng',
            'phone' => '08034567890',
            'payment_terms_days' => 30,
        ]);

        $client = Client::firstOrFail();
        $response->assertRedirect(route('clients.show', $client));
        $this->assertSame('Eze Bakeries', $client->company);
    }

    public function test_a_client_needs_a_name(): void
    {
        $this->post(route('clients.store'), ['name' => '', 'payment_terms_days' => 14])
            ->assertSessionHasErrors('name');
    }

    public function test_client_list_shows_outstanding_amounts(): void
    {
        $client = Client::factory()->create(['name' => 'Kemi Adebayo']);

        $this->get(route('clients.index'))->assertOk()->assertSee('Kemi Adebayo');
    }
}

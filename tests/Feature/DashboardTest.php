<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_no_data(): void
    {
        $this->get('/')->assertOk()->assertSee('Outstanding')->assertSee('No invoices yet.');
    }

    public function test_dashboard_totals_outstanding_sent_invoices(): void
    {
        Invoice::factory()->sent()->withLines([['Work', 1, 100000]])->create();
        Invoice::factory()->withLines([['Draft work', 1, 999999]])->create();

        $this->get('/')->assertOk()->assertSee('₦107,500.00');
    }

    public function test_seeded_demo_data_renders(): void
    {
        $this->seed();

        $this->get('/')->assertOk();
        $this->get(route('invoices.index'))->assertOk()->assertSee('INV-0014');
        $this->get(route('clients.index'))->assertOk()->assertSee('Kora Foods Ltd');
    }
}

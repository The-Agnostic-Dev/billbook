<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_invoice_calculates_discounts_and_vat(): void
    {
        $client = Client::factory()->create();

        $response = $this->post(route('invoices.store'), [
            'client_id' => $client->id,
            'issue_date' => today()->toDateString(),
            'due_date' => today()->addDays(14)->toDateString(),
            'items' => [
                ['description' => 'Logo design', 'quantity' => 1, 'unit_price' => 200000, 'discount_percent' => 10],
                ['description' => 'Business cards', 'quantity' => 2, 'unit_price' => 25000],
            ],
        ]);

        $invoice = Invoice::firstOrFail();
        $response->assertRedirect(route('invoices.show', $invoice));

        $this->assertSame('INV-0001', $invoice->number);
        $this->assertEquals(230000, $invoice->subtotal);
        $this->assertEquals(20000, $invoice->discount_total);
        $this->assertEquals(17250, $invoice->vat_total);
        $this->assertEquals(247250, $invoice->total);
        $this->assertNull($invoice->sent_at);
    }

    public function test_an_invoice_needs_at_least_one_line(): void
    {
        $client = Client::factory()->create();

        $this->post(route('invoices.store'), [
            'client_id' => $client->id,
            'issue_date' => today()->toDateString(),
            'due_date' => today()->addDays(14)->toDateString(),
            'items' => [],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_due_date_cannot_be_before_issue_date(): void
    {
        $client = Client::factory()->create();

        $this->post(route('invoices.store'), [
            'client_id' => $client->id,
            'issue_date' => today()->toDateString(),
            'due_date' => today()->subDay()->toDateString(),
            'items' => [['description' => 'Work', 'quantity' => 1, 'unit_price' => 1000]],
        ])->assertSessionHasErrors('due_date');
    }

    public function test_show_page_lists_lines_and_balance(): void
    {
        $invoice = Invoice::factory()->sent()->withLines([['Copywriting', 4, 10000]])->create();
        $invoice->payments()->create(['amount' => 10000, 'paid_on' => today(), 'method' => 'transfer']);

        $this->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Copywriting')
            ->assertSee('₦43,000.00')
            ->assertSee('₦33,000.00');
    }

    public function test_status_follows_payments_and_due_date(): void
    {
        $draft = Invoice::factory()->withLines([['Work', 1, 1000]])->create();
        $this->assertSame('draft', $draft->status());

        $open = Invoice::factory()->sent()->withLines([['Work', 1, 1000]])->create();
        $this->assertSame('sent', $open->status());

        $late = Invoice::factory()->sent()->withLines([['Work', 1, 1000]])->create(['due_date' => today()->subDays(3)]);
        $this->assertSame('overdue', $late->status());

        $open->payments()->create(['amount' => $open->total, 'paid_on' => today(), 'method' => 'cash']);
        $this->assertSame('paid', $open->fresh()->status());
    }

    public function test_drafts_can_be_deleted_but_sent_invoices_cannot(): void
    {
        $draft = Invoice::factory()->withLines([['Work', 1, 1000]])->create();
        $sent = Invoice::factory()->sent()->withLines([['Work', 1, 1000]])->create();

        $this->delete(route('invoices.destroy', $draft))->assertRedirect(route('invoices.index'));
        $this->delete(route('invoices.destroy', $sent))->assertSessionHas('error');

        $this->assertModelMissing($draft);
        $this->assertModelExists($sent);
    }

    public function test_overdue_filter_lists_only_late_invoices(): void
    {
        Invoice::factory()->sent()->withLines([['Work', 1, 1000]])->create(['number' => 'INV-LATE', 'due_date' => today()->subDays(5)]);
        Invoice::factory()->sent()->withLines([['Work', 1, 1000]])->create(['number' => 'INV-ONTIME', 'due_date' => today()->addDays(5)]);

        $this->get(route('invoices.index', ['status' => 'overdue']))
            ->assertOk()
            ->assertSee('INV-LATE')
            ->assertDontSee('INV-ONTIME');
    }

    public function test_print_view_shows_business_and_client_details(): void
    {
        $invoice = Invoice::factory()->sent()->withLines([['Consulting', 1, 50000]])->create();

        $this->get(route('invoices.print', $invoice))
            ->assertOk()
            ->assertSee(config('billbook.business.name'))
            ->assertSee($invoice->number)
            ->assertSee('₦53,750.00');
    }

    public function test_print_view_shows_amount_paid_and_reduced_balance(): void
    {
        $invoice = Invoice::factory()->sent()->withLines([['Consulting', 1, 50000]])->create();
        $invoice->payments()->create(['amount' => 20000, 'paid_on' => today(), 'method' => 'transfer']);

        $this->get(route('invoices.print', $invoice))
            ->assertOk()
            ->assertSee('₦20,000.00')
            ->assertSee('₦33,750.00');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_a_part_payment_reduces_the_balance(): void
    {
        $invoice = Invoice::factory()->sent()->withLines([['Design', 1, 100000]])->create();

        $this->post(route('invoices.payments.store', $invoice), [
            'amount' => '40000',
            'paid_on' => today()->toDateString(),
            'method' => 'transfer',
            'reference' => 'TRF-1',
        ])->assertRedirect(route('invoices.show', $invoice))->assertSessionHas('status');

        $invoice->refresh();
        $this->assertEquals(40000, $invoice->amount_paid);
        $this->assertEquals(67500, $invoice->balance);
        $this->assertSame('partial', $invoice->status());
    }

    public function test_payments_cannot_be_dated_in_the_future(): void
    {
        $invoice = Invoice::factory()->sent()->withLines([['Design', 1, 100000]])->create();

        $this->post(route('invoices.payments.store', $invoice), [
            'amount' => '1000',
            'paid_on' => today()->addDays(2)->toDateString(),
            'method' => 'transfer',
        ])->assertSessionHasErrors('paid_on');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_drafts_do_not_take_payments(): void
    {
        $invoice = Invoice::factory()->withLines([['Design', 1, 100000]])->create();

        $this->post(route('invoices.payments.store', $invoice), [
            'amount' => '1000',
            'paid_on' => today()->toDateString(),
            'method' => 'cash',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
    }
}

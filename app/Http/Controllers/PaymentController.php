<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->sent_at === null) {
            return back()->with('error', 'Mark the invoice as sent before recording payments.');
        }

        $request->merge(['amount' => Money::parse($request->input('amount'))]);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:100'],
        ], [
            'paid_on.before_or_equal' => 'The payment date cannot be in the future.',
        ]);

        $invoice->payments()->create($data);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Payment of '.naira($data['amount']).' recorded.');
    }
}

@extends('layouts.app')

@section('title', $invoice->number)

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $invoice->number }} @include('partials.status')</h1>
            <p class="muted">
                <a href="{{ route('clients.show', $invoice->client) }}">{{ $invoice->client->displayName() }}</a>
                · Issued <span data-field="issue-date">{{ $invoice->issue_date->format('j M Y') }}</span>
                · Due <span data-field="due-date">{{ $invoice->due_date->format('j M Y') }}</span>
                @if ($invoice->sent_at) · Sent {{ $invoice->sent_at->format('j M Y') }} @endif
            </p>
        </div>
        <div class="actions">
            <a href="{{ route('invoices.edit', $invoice) }}" class="button secondary">Edit</a>
            <a href="{{ route('invoices.print', $invoice) }}" class="button secondary" target="_blank">Print / PDF</a>
            <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}">
                @csrf
                <button type="submit" class="secondary">Duplicate for next month</button>
            </form>
            @if (! $invoice->sent_at)
                <form method="POST" action="{{ route('invoices.send', $invoice) }}">
                    @csrf
                    <button type="submit">Mark as sent</button>
                </form>
                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this draft?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="danger">Delete draft</button>
                </form>
            @endif
        </div>
    </div>

    <section class="panel">
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="num">Qty</th>
                    <th class="num">Unit price</th>
                    <th class="num">Discount</th>
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="num">{{ rtrim(rtrim($item->quantity, '0'), '.') }}</td>
                        <td class="num">{{ naira($item->unit_price) }}</td>
                        <td class="num">{{ (float) $item->discount_percent ? rtrim(rtrim($item->discount_percent, '0'), '.').'%' : '' }}</td>
                        <td class="num">{{ naira($item->line_total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <dl class="totals">
            <dt>Subtotal</dt><dd data-total="gross">{{ naira($invoice->subtotal + $invoice->discount_total) }}</dd>
            @if ((float) $invoice->discount_total > 0)
                <dt>Discount</dt><dd data-total="discount">-{{ naira($invoice->discount_total) }}</dd>
            @endif
            <dt>VAT ({{ config('billbook.vat_rate') * 100 }}%)</dt><dd data-total="vat">{{ naira($invoice->vat_total) }}</dd>
            <dt class="grand">Total</dt><dd class="grand" data-total="total">{{ naira($invoice->total) }}</dd>
            <dt>Paid</dt><dd data-total="paid">{{ naira($invoice->amount_paid) }}</dd>
            <dt class="grand">Balance due</dt><dd class="grand" data-total="balance">{{ naira($invoice->balance) }}</dd>
        </dl>

        @if ($invoice->notes)
            <p class="notes-display">{{ $invoice->notes }}</p>
        @endif
    </section>

    <section class="panel">
        <h2>Payments</h2>
        @if ($invoice->payments->isEmpty())
            <p class="muted">No payments recorded yet.</p>
        @else
            <table>
                <thead>
                    <tr><th>Date</th><th>Method</th><th>Reference</th><th class="num">Amount</th></tr>
                </thead>
                <tbody>
                    @foreach ($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_on->format('j M Y') }}</td>
                            <td>{{ $payment->methodLabel() }}</td>
                            <td>{{ $payment->reference }}</td>
                            <td class="num">{{ naira($payment->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($invoice->sent_at && $invoice->balance > 0)
            <h3>Record a payment</h3>
            @include('partials.errors')
            <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}" class="form-grid payment-form">
                @csrf
                <label>
                    Amount (₦)
                    <input type="text" name="amount" inputmode="decimal" value="{{ old('amount') }}" placeholder="0.00" required>
                </label>
                <label>
                    Date received
                    <input type="date" name="paid_on" value="{{ old('paid_on', today()->toDateString()) }}" required>
                </label>
                <label>
                    Method
                    <select name="method">
                        @foreach ($methods as $value => $label)
                            <option value="{{ $value }}" @selected(old('method', 'transfer') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Reference
                    <input type="text" name="reference" value="{{ old('reference') }}" placeholder="Bank ref, cheque no.">
                </label>
                <div class="actions">
                    <button type="submit">Record payment</button>
                </div>
            </form>
        @elseif (! $invoice->sent_at)
            <p class="muted">Mark the invoice as sent to start recording payments.</p>
        @endif
    </section>
@endsection

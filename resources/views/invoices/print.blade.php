<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number }} · {{ $business['name'] }}</title>
    <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>
<body>
    <div class="toolbar no-print">
        <a href="{{ route('invoices.show', $invoice) }}">&larr; Back to invoice</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <article class="sheet">
        <header>
            <div>
                <h1>{{ $business['name'] }}</h1>
                <p>{{ $business['address'] }}<br>{{ $business['email'] }} · {{ $business['phone'] }}<br>TIN {{ $business['tin'] }}</p>
            </div>
            <div class="meta">
                <h2>INVOICE</h2>
                <table>
                    <tr><th>Number</th><td>{{ $invoice->number }}</td></tr>
                    <tr><th>Issue date</th><td>{{ $invoice->issue_date->format('j F Y') }}</td></tr>
                    <tr><th>Due date</th><td>{{ $invoice->due_date->format('j F Y') }}</td></tr>
                </table>
            </div>
        </header>

        <section class="bill-to">
            <h3>Bill to</h3>
            <p>
                <strong>{{ $invoice->client->company ?: $invoice->client->name }}</strong><br>
                @if ($invoice->client->company)Attn: {{ $invoice->client->name }}<br>@endif
                @if ($invoice->client->address){{ $invoice->client->address }}<br>@endif
                {{ $invoice->client->email }}
            </p>
        </section>

        <table class="items">
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

        <table class="summary">
            <tr><th>Subtotal</th><td>{{ naira($invoice->subtotal + $invoice->discount_total) }}</td></tr>
            @if ((float) $invoice->discount_total > 0)
                <tr><th>Discount</th><td>-{{ naira($invoice->discount_total) }}</td></tr>
            @endif
            <tr><th>VAT ({{ config('billbook.vat_rate') * 100 }}%)</th><td>{{ naira($invoice->vat_total) }}</td></tr>
            <tr class="grand"><th>Total</th><td>{{ naira($invoice->total) }}</td></tr>
            @if ($invoice->amount_paid > 0)
                <tr><th>Amount paid</th><td>-{{ naira($invoice->amount_paid) }}</td></tr>
            @endif
            <tr class="grand"><th>Balance due</th><td data-total="balance">{{ naira($invoice->balance) }}</td></tr>
        </table>

        <footer>
            @if ($invoice->notes)<p>{{ $invoice->notes }}</p>@endif
            <p>Please pay by {{ $invoice->due_date->format('j F Y') }} to {{ $business['bank'] }}, quoting {{ $invoice->number }}.</p>
        </footer>
    </article>
</body>
</html>

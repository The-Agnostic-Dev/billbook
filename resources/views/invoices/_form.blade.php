@include('partials.errors')

<div class="form-grid">
    <label>
        Client
        <select name="client_id" required>
            <option value="">Choose a client</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected((int) old('client_id', $invoice->client_id) === $client->id)>{{ $client->displayName() }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Issue date
        <input type="date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date?->toDateString()) }}" required>
    </label>
    <label>
        Due date
        <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->toDateString()) }}" required>
    </label>
</div>

<h2>Line items</h2>
<table class="lines" id="line-items" data-vat-rate="{{ config('billbook.vat_rate') }}">
    <thead>
        <tr>
            <th>Description</th>
            <th class="num">Qty</th>
            <th class="num">Unit price (₦)</th>
            <th class="num">Discount %</th>
            <th class="num">Amount</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach (old('items', $items) as $index => $item)
            @include('invoices._line', ['index' => $index, 'item' => $item])
        @endforeach
    </tbody>
</table>
<template id="line-template">
    @include('invoices._line', ['index' => '__INDEX__', 'item' => ['description' => '', 'quantity' => 1, 'unit_price' => '', 'discount_percent' => 0]])
</template>
<p><button type="button" class="secondary" id="add-line">Add line</button></p>

<div class="form-bottom">
    <label class="notes">
        Notes for the client
        <textarea name="notes" rows="4" placeholder="Payment instructions, thank you note...">{{ old('notes', $invoice->notes) }}</textarea>
    </label>

    <dl class="totals" id="form-totals">
        <dt>Subtotal</dt><dd data-total="gross">₦0.00</dd>
        <dt>Discount</dt><dd data-total="discount">₦0.00</dd>
        <dt>VAT ({{ config('billbook.vat_rate') * 100 }}%)</dt><dd data-total="vat">₦0.00</dd>
        <dt class="grand">Total</dt><dd class="grand" data-total="total">₦0.00</dd>
    </dl>
</div>

@push('scripts')
    <script src="{{ asset('js/invoice-form.js') }}" defer></script>
@endpush

@extends('layouts.app')

@section('title', $client->name)

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $client->name }}</h1>
            @if ($client->company)<p class="muted">{{ $client->company }}</p>@endif
        </div>
        <div class="actions">
            <a href="{{ route('clients.edit', $client) }}" class="button secondary">Edit</a>
            <a href="{{ route('invoices.create', ['client' => $client->id]) }}" class="button">New invoice</a>
        </div>
    </div>

    <section class="panel details">
        <dl>
            <dt>Email</dt><dd>{{ $client->email ?: 'Not set' }}</dd>
            <dt>Phone</dt><dd>{{ $client->phone ?: 'Not set' }}</dd>
            <dt>Address</dt><dd>{{ $client->address ?: 'Not set' }}</dd>
            <dt>Payment terms</dt><dd>{{ $client->payment_terms_days }} days</dd>
            <dt>Outstanding</dt><dd><strong>{{ naira($client->outstanding()) }}</strong></dd>
        </dl>
    </section>

    <h2>Invoices</h2>
    <table>
        <thead>
            <tr>
                <th>Number</th>
                <th>Issued</th>
                <th>Due</th>
                <th class="num">Total</th>
                <th class="num">Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($client->invoices as $invoice)
                <tr>
                    <td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->number }}</a></td>
                    <td>{{ $invoice->issue_date->format('j M Y') }}</td>
                    <td>{{ $invoice->due_date->format('j M Y') }}</td>
                    <td class="num">{{ naira($invoice->total) }}</td>
                    <td class="num">{{ naira($invoice->balance) }}</td>
                    <td>@include('partials.status')</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No invoices for this client yet.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection

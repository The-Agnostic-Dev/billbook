@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Dashboard</h1>

    <section class="stats">
        <div class="stat">
            <span class="label">Outstanding</span>
            <strong data-stat="outstanding">{{ naira($outstanding) }}</strong>
            <span class="muted">{{ $openCount }} open {{ Str::plural('invoice', $openCount) }}</span>
        </div>
        <div class="stat stat-warn">
            <span class="label">Overdue</span>
            <strong data-stat="overdue">{{ naira($overdueTotal) }}</strong>
            <span class="muted">{{ $overdueCount }} {{ Str::plural('invoice', $overdueCount) }}</span>
        </div>
        <div class="stat">
            <span class="label">Collected this month</span>
            <strong data-stat="collected">{{ naira($collectedThisMonth) }}</strong>
            <span class="muted">{{ today()->format('F Y') }}</span>
        </div>
        <div class="stat">
            <span class="label">Invoiced this month</span>
            <strong data-stat="invoiced">{{ naira($invoicedThisMonth) }}</strong>
            <span class="muted">{{ $draftCount }} {{ Str::plural('draft', $draftCount) }} not sent yet</span>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Recent invoices</h2>
            <a href="{{ route('invoices.index') }}">View all</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Client</th>
                    <th>Issued</th>
                    <th class="num">Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recent as $invoice)
                    <tr>
                        <td><a href="{{ route('invoices.show', $invoice->client) }}">{{ $invoice->number }}</a></td>
                        <td><a href="{{ route('clients.show', $invoice->client) }}">{{ $invoice->client->name }}</a></td>
                        <td>{{ $invoice->issue_date->format('j M Y') }}</td>
                        <td class="num">{{ naira($invoice->total) }}</td>
                        <td>@include('partials.status')</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection

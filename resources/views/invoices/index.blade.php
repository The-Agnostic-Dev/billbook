@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
    <div class="page-head">
        <h1>Invoices</h1>
        <a href="{{ route('invoices.create') }}" class="button">New invoice</a>
    </div>

    <div class="toolbar">
        <nav class="tabs" aria-label="Filter invoices">
            @foreach ($filters as $key => $label)
                <a href="{{ route('invoices.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => $search ?: null])) }}"
                   @class(['tab', 'active' => $filter === $key])>{{ $label }}</a>
            @endforeach
        </nav>
        <form method="GET" action="{{ route('invoices.index') }}" class="search">
            @if ($filter !== 'all')<input type="hidden" name="status" value="{{ $filter }}">@endif
            <input type="search" name="q" value="{{ $search }}" placeholder="Invoice number or client" aria-label="Search invoices">
            <button type="submit" class="secondary">Search</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Number</th>
                <th>Client</th>
                <th>Issued</th>
                <th>Due</th>
                <th class="num">Total</th>
                <th class="num">Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoices as $invoice)
                <tr data-number="{{ $invoice->number }}">
                    <td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->number }}</a></td>
                    <td>{{ $invoice->client->displayName() }}</td>
                    <td>{{ $invoice->issue_date->format('j M Y') }}</td>
                    <td>{{ $invoice->due_date->format('j M Y') }}</td>
                    <td class="num">{{ naira($invoice->total) }}</td>
                    <td class="num">{{ naira($invoice->balance) }}</td>
                    <td>@include('partials.status')</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No invoices found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $invoices->links('pagination::default') }}
    </div>
@endsection

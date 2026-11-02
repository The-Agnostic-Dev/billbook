@extends('layouts.app')

@section('title', 'Clients')

@section('content')
    <div class="page-head">
        <h1>Clients</h1>
        <a href="{{ route('clients.create') }}" class="button">Add client</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th class="num">Invoices</th>
                <th class="num">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clients as $client)
                <tr>
                    <td>
                        <a href="{{ route('clients.show', $client) }}">{{ $client->name }}</a>
                        @if ($client->company)<br><span class="muted">{{ $client->company }}</span>@endif
                    </td>
                    <td>{{ $client->email }}</td>
                    <td>{{ $client->phone }}</td>
                    <td class="num">{{ $client->invoices_count }}</td>
                    <td class="num">{{ naira($client->outstanding()) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No clients yet. Add your first one to start invoicing.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection

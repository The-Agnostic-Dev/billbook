@extends('layouts.app')

@section('title', 'Edit '.$invoice->number)

@section('content')
    <h1>Edit {{ $invoice->number }}</h1>
    <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="panel" id="invoice-form">
        @csrf
        @method('PUT')
        @include('invoices._form')
        <div class="actions">
            <button type="submit">Save changes</button>
            <a href="{{ route('invoices.show', $invoice) }}">Cancel</a>
        </div>
    </form>
@endsection

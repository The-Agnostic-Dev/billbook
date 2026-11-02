@extends('layouts.app')

@section('title', 'New invoice')

@section('content')
    <h1>New invoice</h1>
    <form method="POST" action="{{ route('invoices.store') }}" class="panel" id="invoice-form">
        @csrf
        @include('invoices._form')
        <div class="actions">
            <button type="submit">Save draft</button>
            <a href="{{ route('invoices.index') }}">Cancel</a>
        </div>
    </form>
@endsection

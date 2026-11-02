@extends('layouts.app')

@section('title', 'New client')

@section('content')
    <h1>New client</h1>
    <form method="POST" action="{{ route('clients.store') }}" class="panel">
        @csrf
        @include('clients._form')
        <div class="actions">
            <button type="submit">Save client</button>
            <a href="{{ route('clients.index') }}">Cancel</a>
        </div>
    </form>
@endsection

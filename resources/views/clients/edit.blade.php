@extends('layouts.app')

@section('title', 'Edit '.$client->name)

@section('content')
    <h1>Edit {{ $client->name }}</h1>
    <form method="POST" action="{{ route('clients.update', $client) }}" class="panel">
        @csrf
        @method('PUT')
        @include('clients._form')
        <div class="actions">
            <button type="submit">Save changes</button>
            <a href="{{ route('clients.show', $client) }}">Cancel</a>
        </div>
    </form>
@endsection

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Billbook') · Billbook</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="topbar">
        <a href="{{ route('dashboard') }}" class="brand">Billbook</a>
        <nav>
            <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>Dashboard</a>
            <a href="{{ route('invoices.index') }}" @class(['active' => request()->routeIs('invoices.*')])>Invoices</a>
            <a href="{{ route('clients.index') }}" @class(['active' => request()->routeIs('clients.*')])>Clients</a>
        </nav>
        <a href="{{ route('invoices.create') }}" class="button">New invoice</a>
    </header>

    <main class="container">
        @if (session('status'))
            <div class="flash flash-ok" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash-error" role="alert">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>

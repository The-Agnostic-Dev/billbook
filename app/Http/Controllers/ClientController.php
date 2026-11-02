<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::query()
            ->withCount('invoices')
            ->with(['invoices' => fn ($query) => $query->sent()->withSum('payments', 'amount')])
            ->orderBy('name')
            ->get();

        return view('clients.index', ['clients' => $clients]);
    }

    public function create(): View
    {
        return view('clients.create', [
            'client' => new Client(['payment_terms_days' => config('billbook.default_payment_terms')]),
        ]);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = Client::create($request->validated());

        return redirect()->route('clients.show', $client)->with('status', "{$client->name} added.");
    }

    public function show(Client $client): View
    {
        $client->load(['invoices' => fn ($query) => $query->withSum('payments', 'amount')->orderByDesc('issue_date')]);

        return view('clients.show', ['client' => $client]);
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', ['client' => $client]);
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()->route('clients.show', $client)->with('status', 'Client details saved.');
    }
}

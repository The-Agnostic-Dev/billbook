<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public const FILTERS = [
        'all' => 'All',
        'draft' => 'Drafts',
        'unpaid' => 'Unpaid',
        'overdue' => 'Overdue',
        'paid' => 'Paid',
    ];

    public function index(Request $request): View
    {
        $filter = array_key_exists($request->query('status'), self::FILTERS) ? $request->query('status') : 'all';
        $search = trim((string) $request->query('q', ''));

        $invoices = Invoice::query()
            ->with('client')
            ->withSum('payments', 'amount')
            ->when($filter !== 'all', fn ($query) => $query->{$filter}())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('number', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($client) => $client
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('company', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(config('billbook.per_page'))
            ->withQueryString();

        return view('invoices.index', [
            'invoices' => $invoices,
            'filter' => $filter,
            'search' => $search,
            'filters' => self::FILTERS,
        ]);
    }

    public function create(Request $request): View
    {
        $client = Client::find($request->query('client'));
        $terms = $client?->payment_terms_days ?? config('billbook.default_payment_terms');

        $invoice = new Invoice([
            'client_id' => $client?->id,
            'issue_date' => today(),
            'due_date' => today()->addDays($terms),
        ]);

        return view('invoices.create', [
            'invoice' => $invoice,
            'clients' => Client::orderBy('name')->get(),
            'items' => [['description' => '', 'quantity' => 1, 'unit_price' => '', 'discount_percent' => 0]],
        ]);
    }

    public function store(InvoiceRequest $request): RedirectResponse
    {
        $invoice = DB::transaction(function () use ($request) {
            $invoice = Invoice::create([
                ...$request->safe()->except('items'),
                'number' => Invoice::nextNumber(),
            ]);
            $invoice->syncItems($request->validated('items'));

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('status', "Invoice {$invoice->number} created.");
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['client', 'items', 'payments']);

        return view('invoices.show', [
            'invoice' => $invoice,
            'methods' => Payment::METHODS,
        ]);
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load('items');

        return view('invoices.edit', [
            'invoice' => $invoice,
            'clients' => Client::orderBy('name')->get(),
            'items' => $invoice->items->map->only(['id', 'description', 'quantity', 'unit_price', 'discount_percent'])->all(),
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        DB::transaction(function () use ($request, $invoice) {
            $invoice->update($request->safe()->except('items'));
            $invoice->syncItems($request->validated('items'));
        });

        return redirect()->route('invoices.show', $invoice)->with('status', "Invoice {$invoice->number} updated.");
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->sent_at !== null) {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('status', "Draft {$invoice->number} deleted.");
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        $invoice->update(['sent_at' => $invoice->sent_at ?? now()]);

        return redirect()->route('invoices.show', $invoice)->with('status', "Invoice {$invoice->number} marked as sent.");
    }

    public function duplicate(Invoice $invoice): RedirectResponse
    {
        $copy = DB::transaction(function () use ($invoice) {
            $copy = Invoice::create([
                'client_id' => $invoice->client_id,
                'number' => Invoice::nextNumber(),
                'issue_date' => $invoice->issue_date->copy()->addMonth(),
                'due_date' => $invoice->due_date->copy()->addMonth(),
                'notes' => $invoice->notes,
            ]);
            $copy->syncItems($invoice->items->map->only(['description', 'quantity', 'unit_price', 'discount_percent'])->all());

            return $copy;
        });

        return redirect()->route('invoices.edit', $copy)->with('status', "Draft {$copy->number} created for next month. Check it over before sending.");
    }

    public function print(Invoice $invoice): View
    {
        $invoice->load(['client', 'items', 'payments']);

        return view('invoices.print', ['invoice' => $invoice, 'business' => config('billbook.business')]);
    }
}

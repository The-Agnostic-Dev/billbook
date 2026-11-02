<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $open = Invoice::sent()
            ->with('client')
            ->withSum('payments', 'amount')
            ->get()
            ->filter(fn (Invoice $invoice) => $invoice->balance > 0);

        $overdue = $open->filter(fn (Invoice $invoice) => $invoice->isOverdue());

        $monthStart = today()->startOfMonth()->toDateString();
        $monthEnd = today()->endOfMonth()->toDateString();

        return view('dashboard', [
            'outstanding' => round($open->sum(fn (Invoice $i) => $i->balance), 2),
            'openCount' => $open->count(),
            'overdueTotal' => round($overdue->sum(fn (Invoice $i) => $i->balance), 2),
            'overdueCount' => $overdue->count(),
            'collectedThisMonth' => (float) Payment::whereBetween('paid_on', [$monthStart, $monthEnd])->sum('amount'),
            'invoicedThisMonth' => (float) Invoice::sent()->whereBetween('issue_date', [$monthStart, $monthEnd])->sum('total'),
            'draftCount' => Invoice::draft()->count(),
            'recent' => Invoice::with('client')
                ->withSum('payments', 'amount')
                ->orderByDesc('issue_date')
                ->orderByDesc('id')
                ->take(6)
                ->get(),
        ]);
    }
}

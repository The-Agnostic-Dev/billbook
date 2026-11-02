<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data for a small design studio. Dates are relative to today so the
     * dashboard always has something current to show.
     */
    public function run(): void
    {
        $clients = collect([
            ['name' => 'Adaeze Nwankwo', 'company' => 'Kora Foods Ltd', 'email' => 'adaeze@korafoods.ng', 'phone' => '08031234567', 'address' => '3 Akin Adesola St, Victoria Island, Lagos', 'payment_terms_days' => 14],
            ['name' => 'Babatunde Ola', 'company' => 'Ola & Sons Logistics', 'email' => 'tunde@olasons.com', 'phone' => '08127654321', 'address' => '22 Apapa Rd, Ebute Metta, Lagos', 'payment_terms_days' => 30],
            ['name' => 'Chioma Uba', 'company' => 'Brightpath Schools', 'email' => 'bursar@brightpath.edu.ng', 'phone' => '07035550192', 'address' => '9 Okpara Ave, Enugu', 'payment_terms_days' => 14],
            ['name' => 'David Mensah', 'company' => null, 'email' => 'david.mensah@gmail.com', 'phone' => '09024441188', 'address' => 'Gwarinpa, Abuja', 'payment_terms_days' => 7],
            ['name' => 'Efe Okoro', 'company' => 'Lagoon Realty', 'email' => 'efe@lagoonrealty.ng', 'phone' => '08186669900', 'address' => '5 Admiralty Rd, Lekki, Lagos', 'payment_terms_days' => 30],
        ])->map(fn (array $attributes) => Client::create($attributes))->values();

        // [number, client, issued (days from today), due (days from today), sent, items, payments]
        // items: [description, quantity, unit price, discount %]
        // payments: [amount or 'full', paid (days from today), method]
        $invoices = [
            ['INV-0001', 0, -75, -61, true, [['Brand identity refresh', 1, 450000, 0], ['Packaging artwork, 3 SKUs', 3, 85000, 0]], [['full', -60, 'transfer']]],
            ['INV-0002', 1, -60, -30, true, [['Fleet livery design', 1, 320000, 10]], [['full', -28, 'transfer']]],
            ['INV-0003', 2, -50, -36, true, [['School prospectus layout', 1, 280000, 0], ['Photography day rate', 2, 95000, 0]], [[200000, -33, 'transfer']]],
            ['INV-0004', 0, -40, -26, true, [['Social media templates', 12, 15000, 0]], []],
            ['INV-0005', 3, -35, -5, true, [['Website maintenance, monthly', 1, 120000, 0], ['Hosting renewal', 1, 48000, 0]], [[60000, -20, 'card']]],
            ['INV-0006', 4, -30, 0, true, [['Property brochure design', 1, 210000, 0], ['Print management', 1, 35000, 0]], []],
            ['INV-0008', 1, -20, 10, true, [['Delivery app UI design', 40, 12500, 5]], [[250000, -8, 'transfer']]],
            ['INV-0009', 2, -14, 16, true, [['Graduation programme design', 1, 95000, 0], ['Banner artwork', 4, 18000, 0]], []],
            ['INV-0010', 3, -10, 20, true, [['Landing page copy and design', 1, 175000, 0]], [['full', -2, 'transfer']]],
            ['INV-0011', 0, -7, 7, true, [['Menu redesign', 1, 140000, 0], ['Illustrations', 6, 22500, 10]], [[100000, -1, 'transfer']]],
            ['INV-0012', 4, -3, 27, false, [['Listing photography', 3, 65000, 0]], []],
            ['INV-0013', 1, -1, 29, false, [['Quarterly report design', 1, 230000, 0]], []],
            ['INV-0014', 2, 0, 14, true, [['Website content update', 5, 30000, 0]], []],
        ];

        foreach ($invoices as [$number, $clientIndex, $issued, $due, $sent, $items, $payments]) {
            $invoice = Invoice::create([
                'client_id' => $clients[$clientIndex]->id,
                'number' => $number,
                'issue_date' => today()->addDays($issued),
                'due_date' => today()->addDays($due),
                'sent_at' => $sent ? today()->addDays($issued)->setTime(10, 30) : null,
                'notes' => 'Thank you for your business.',
            ]);

            $invoice->syncItems(array_map(fn (array $item) => [
                'description' => $item[0],
                'quantity' => $item[1],
                'unit_price' => $item[2],
                'discount_percent' => $item[3],
            ], $items));

            $invoice->refresh();

            foreach ($payments as [$amount, $paidOn, $method]) {
                $invoice->payments()->create([
                    'amount' => $amount === 'full' ? $invoice->total : $amount,
                    'paid_on' => today()->addDays($paidOn),
                    'method' => $method,
                    'reference' => strtoupper(substr($method, 0, 3)).'-'.random_int(100000, 999999),
                ]);
            }
        }
    }
}

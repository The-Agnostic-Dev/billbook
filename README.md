# Billbook

Billbook is a small invoicing app for freelancers and small firms. It keeps a list of clients, builds invoices from line items (quantities, unit prices, per-line discounts and 7.5% VAT), tracks due dates and part payments, and produces a clean printable invoice. The dashboard shows what is outstanding, what is overdue and what came in this month.

Built with Laravel 12, Blade and SQLite. There is no JavaScript build step: the only script is `public/js/invoice-form.js`, which keeps the totals on the invoice form live.

## Requirements

- PHP 8.3 with the `pdo_sqlite` extension
- Composer 2

## Running it

### Docker

```bash
docker compose up --build
```

Open http://localhost:5107. The container migrates and reseeds the demo database every time it starts.

### Natively

```bash
composer setup                      # install, create .env and the SQLite file, migrate and seed
php artisan serve --port=5107       # or: composer dev
```

Run `php artisan migrate:fresh --seed` at any time to reset the demo data. Seeded dates are relative to today, so there is always something due, overdue and recently paid.

## Tests

```bash
php artisan test
```

Feature tests use an in-memory SQLite database (see `phpunit.xml`), so they never touch your demo data.

## Where things live

| Path | Purpose |
| --- | --- |
| `app/Models/Invoice.php` | Totals, balance, status rules, list filters (scopes) and invoice numbering |
| `app/Models/InvoiceItem.php` | Line totals; keeps the parent invoice totals in sync |
| `app/Http/Controllers` | Dashboard, clients, invoices (incl. send, duplicate, print) and payments |
| `app/Http/Requests` | Validation for clients and invoices |
| `app/Support/Money.php` | Formatting and parsing of naira amounts (`naira()` helper in views) |
| `resources/views` | Blade templates; `invoices/print.blade.php` is the printable invoice |
| `public/js/invoice-form.js` | Add/remove lines and live totals on the invoice form |
| `config/billbook.php` | Business details for the printed invoice, VAT rate, numbering prefix, page size |
| `database/seeders/DatabaseSeeder.php` | Demo clients, invoices and payments |

## How invoices work

- An invoice starts as a **draft**. Drafts can be edited or deleted; **Mark as sent** makes it live.
- Line amount = quantity x unit price, less the line discount. VAT (7.5%) is charged on the discounted subtotal.
- Totals are stored on the invoice and recalculated whenever its lines change.
- Status is derived: *Draft*, *Sent*, *Part paid*, *Overdue* (past the due date with a balance) and *Paid*.
- Payments can only be recorded against sent invoices. Balance due = total minus payments.
- **Duplicate for next month** copies an invoice as a new draft with both dates moved on by one calendar month, for retainers and other monthly work.
- Invoice numbers are sequential: `INV-0001`, `INV-0002` and so on.

## Configuration

Business details shown on printed invoices come from `config/billbook.php` and can be overridden with `BILLBOOK_*` variables in `.env` (see the config file for names). `APP_TIMEZONE` defaults to `Africa/Lagos`.

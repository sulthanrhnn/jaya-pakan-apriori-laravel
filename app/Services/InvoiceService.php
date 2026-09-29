<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class InvoiceService
{
    public function generate(): string
    {
        do {
            $invoice = 'JPU-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('invoice_number', $invoice)->exists());

        return $invoice;
    }
}

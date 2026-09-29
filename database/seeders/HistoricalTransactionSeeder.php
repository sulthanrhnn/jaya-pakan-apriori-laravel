<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = json_decode((string) file_get_contents(database_path('data/transactions.json')), true);
        $items = json_decode((string) file_get_contents(database_path('data/transaction_items.json')), true);

        if (! is_array($transactions) || count($transactions) !== 300) {
            throw new RuntimeException('Data transaksi historis harus berjumlah 300 record.');
        }

        if (! is_array($items) || count($items) !== 841) {
            throw new RuntimeException('Detail transaksi historis harus berjumlah 841 baris.');
        }

        $itemsByInvoice = collect($items)->groupBy('invoice_number');
        $productIds = Product::withTrashed()->pluck('id', 'code');

        DB::transaction(function () use ($transactions, $itemsByInvoice, $productIds): void {
            foreach ($transactions as $transaction) {
                $date = Carbon::parse($transaction['ordered_at'])->setTime(12, 0);

                $order = Order::query()->updateOrCreate(
                    ['invoice_number' => $transaction['invoice_number']],
                    [
                        'source' => 'historical',
                        'status' => 'completed',
                        'payment_status' => 'verified',
                        'customer_name' => null,
                        'customer_phone' => null,
                        'customer_address' => null,
                        'customer_note' => 'Impor dataset penelitian',
                        'subtotal' => (int) $transaction['total'],
                        'grand_total' => (int) $transaction['total'],
                        'payment_method' => 'historical_import',
                        'payment_proof_path' => null,
                        'payment_submitted_at' => null,
                        'ordered_at' => $date,
                    ]
                );

                $order->items()->delete();

                $rows = $itemsByInvoice->get($transaction['invoice_number'], collect());
                foreach ($rows as $row) {
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $productIds[$row['product_code']] ?? null,
                        'product_code' => $row['product_code'],
                        'product_name' => $row['product_name'],
                        'quantity' => (int) $row['quantity'],
                        'unit_price' => (int) $row['unit_price'],
                        'subtotal' => (int) $row['subtotal'],
                    ]);
                }
            }
        });
    }
}

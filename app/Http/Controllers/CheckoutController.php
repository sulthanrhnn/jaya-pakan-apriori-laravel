<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\Setting;
use App\Services\AprioriService;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function show(Request $request, AprioriService $apriori): View|RedirectResponse
    {
        [$items, $subtotal, $codes, $stockIssue] = $this->cartDetails($request);
        if ($items->isEmpty()) return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        if ($stockIssue) return redirect()->route('cart.index')->with('error', $stockIssue);

        $recommendations = $apriori->recommendationsForCodes($codes);
        $bank = $this->bankSettings();

        return view('storefront.checkout', compact('items', 'subtotal', 'recommendations', 'bank'));
    }

    public function store(Request $request, InvoiceService $invoiceService): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'min:3', 'max:120'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'customer_address' => ['required', 'string', 'min:10', 'max:1000'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'payment_proof.required' => 'Bukti transfer wajib diunggah.',
            'payment_proof.mimes' => 'Bukti transfer harus JPG, JPEG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran bukti transfer maksimal 5 MB.',
        ]);

        [$items, , , $stockIssue] = $this->cartDetails($request);
        if ($items->isEmpty()) return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        if ($stockIssue) return redirect()->route('cart.index')->with('error', $stockIssue);

        $proofPath = $request->file('payment_proof')->store('payment-proofs', 'local');
        if (! $proofPath) {
            return back()->withInput()->with('error', 'Bukti transfer gagal disimpan. Periksa permission folder storage lalu coba lagi.');
        }

        try {
            $order = DB::transaction(function () use ($items, $data, $proofPath, $invoiceService) {
                $lockedProducts = [];
                $subtotal = 0;

                foreach ($items as $line) {
                    $product = Product::query()->lockForUpdate()->findOrFail($line['product']->id);
                    $quantity = (int) $line['quantity'];
                    if (! $product->is_active || $product->stock < $quantity) {
                        throw new \RuntimeException("Stok {$product->name} tidak mencukupi. Silakan perbarui keranjang.");
                    }
                    $lockedProducts[] = [$product, $quantity];
                    $subtotal += $product->price * $quantity;
                }

                $order = Order::query()->create([
                    'invoice_number' => $invoiceService->generate(),
                    'source' => 'web',
                    'status' => 'pending_verification',
                    'payment_status' => 'pending',
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'customer_address' => $data['customer_address'],
                    'customer_note' => $data['customer_note'] ?? null,
                    'subtotal' => $subtotal,
                    'grand_total' => $subtotal,
                    'payment_method' => 'bank_transfer',
                    'payment_proof_path' => $proofPath,
                    'payment_submitted_at' => now(),
                    'ordered_at' => now(),
                ]);

                foreach ($lockedProducts as [$product, $quantity]) {
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_code' => $product->code,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'subtotal' => $product->price * $quantity,
                    ]);
                    $product->decrement('stock', $quantity);
                }

                OrderStatusLog::query()->create([
                    'order_id' => $order->id,
                    'status' => 'pending_verification',
                    'payment_status' => 'pending',
                    'note' => 'Pesanan dibuat dan bukti transfer diterima sistem.',
                ]);

                return $order;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($proofPath);
            return back()->withInput()->with('error', $exception->getMessage());
        }

        $request->session()->forget('cart');
        return redirect()->route('orders.receipt', $order->invoice_number)
            ->with('success', 'Pesanan berhasil dibuat. Simpan nomor invoice untuk memantau status pesanan.');
    }

    public function receipt(string $invoice): View
    {
        $order = Order::query()->where('source', 'web')->where('invoice_number', $invoice)->with('items')->firstOrFail();
        $bank = $this->bankSettings();
        return view('storefront.receipt', compact('order', 'bank'));
    }

    private function cartDetails(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::query()->whereIn('id', array_keys($cart))->where('is_active', true)->get()->keyBy('id');
        $items = collect();
        $subtotal = 0;
        $codes = [];
        $stockIssue = null;

        foreach ($cart as $id => $quantity) {
            $product = $products->get((int) $id);
            if (! $product) {
                $stockIssue ??= 'Salah satu produk di keranjang sudah tidak tersedia. Silakan perbarui keranjang.';
                continue;
            }

            $quantity = max(1, (int) $quantity);
            if ($product->stock < $quantity) {
                $stockIssue ??= "Stok {$product->name} berubah. Tersedia {$product->stock} unit, sedangkan keranjang meminta {$quantity} unit.";
                continue;
            }

            $lineTotal = $product->price * $quantity;
            $items->push(compact('product', 'quantity', 'lineTotal'));
            $subtotal += $lineTotal;
            $codes[] = $product->code;
        }

        return [$items, $subtotal, $codes, $stockIssue];
    }

    private function bankSettings(): array
    {
        return [
            'bank' => Setting::valueOf('bank_name', 'BCA'),
            'number' => Setting::valueOf('bank_account_number', '123456789'),
            'name' => Setting::valueOf('bank_account_name', 'CV. JAYA PAKAN UTAMA'),
        ];
    }
}

<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Services\AprioriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->input('source')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = trim((string) $request->input('q'));
                $q->where(function ($sub) use ($search) {
                    $sub->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->latest('ordered_at')
            ->paginate(20)
            ->withQueryString();

        return view('staff.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'statusLogs.user']);
        return view('staff.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order, AprioriService $apriori): RedirectResponse
    {
        if ($order->source === 'historical') {
            return back()->with('error', 'Transaksi historis penelitian bersifat read-only.');
        }
        if ($order->status === 'cancelled') {
            return back()->with('error', 'Pesanan yang sudah dibatalkan tidak dapat diaktifkan kembali.');
        }
        if ($order->status === 'completed') {
            return back()->with('error', 'Pesanan yang sudah selesai dikunci agar transaksi dan hasil Apriori tetap konsisten.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Order::STATUSES))],
            'payment_status' => ['required', 'in:'.implode(',', array_keys(Order::PAYMENT_STATUSES))],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($data['status'], ['processing', 'ready', 'shipped', 'completed'], true) && $data['payment_status'] !== 'verified') {
            return back()->with('error', 'Pembayaran harus berstatus terverifikasi sebelum pesanan diproses lebih lanjut.');
        }

        $becameCompleted = false;

        try {
            DB::transaction(function () use ($order, $data, &$becameCompleted): void {
                /** @var Order $locked */
                $locked = Order::query()->lockForUpdate()->with('items')->findOrFail($order->id);
                $previousStatus = $locked->status;

                if ($data['status'] === 'cancelled' && $locked->stock_released_at === null) {
                    foreach ($locked->items as $item) {
                        if ($item->product_id) {
                            $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
                            if ($product) $product->increment('stock', $item->quantity);
                        }
                    }
                    $locked->stock_released_at = now();
                }

                $locked->status = $data['status'];
                $locked->payment_status = $data['payment_status'];
                $locked->save();

                OrderStatusLog::query()->create([
                    'order_id' => $locked->id,
                    'user_id' => auth()->id(),
                    'status' => $data['status'],
                    'payment_status' => $data['payment_status'],
                    'note' => $data['note'] ?? null,
                ]);

                $becameCompleted = $previousStatus !== 'completed' && $data['status'] === 'completed';
            });
        } catch (Throwable $exception) {
            report($exception);
            return back()->with('error', 'Gagal memperbarui status: '.$exception->getMessage());
        }

        if ($becameCompleted) {
            try {
                $apriori->generate();
            } catch (Throwable $exception) {
                report($exception);
                return back()->with('success', 'Status pesanan berhasil diperbarui.')
                    ->with('error', 'Status sudah tersimpan, tetapi proses Apriori ulang gagal: '.$exception->getMessage());
            }
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function paymentProof(Order $order): StreamedResponse
    {
        abort_unless($order->payment_proof_path && Storage::disk('local')->exists($order->payment_proof_path), 404);
        return Storage::disk('local')->response($order->payment_proof_path);
    }
}

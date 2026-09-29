<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AprioriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request, AprioriService $apriori): View
    {
        [$items, $subtotal, $cartCodes] = $this->hydrateCart($request);
        $recommendations = $apriori->recommendationsForCodes($cartCodes);

        return view('storefront.cart', compact('items', 'subtotal', 'recommendations'));
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:999']]);
        $quantity = (int) ($data['quantity'] ?? 1);

        if (! $product->is_active || $product->stock < 1) {
            return back()->with('error', 'Produk sedang tidak tersedia.');
        }

        $cart = $request->session()->get('cart', []);
        $current = (int) ($cart[$product->id] ?? 0);

        if (($current + $quantity) > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya tersedia {$product->stock} unit.");
        }

        $cart[$product->id] = $current + $quantity;
        $request->session()->put('cart', $cart);

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:999']]);
        $quantity = (int) $data['quantity'];

        if ($quantity > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya tersedia {$product->stock} unit.");
        }

        $cart = $request->session()->get('cart', []);
        if (array_key_exists($product->id, $cart)) {
            $cart[$product->id] = $quantity;
            $request->session()->put('cart', $cart);
        }

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'Keranjang dikosongkan.');
    }

    /** @return array{0: \Illuminate\Support\Collection, 1:int, 2:array<int,string>} */
    private function hydrateCart(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::query()->whereIn('id', array_keys($cart))->where('is_active', true)->get()->keyBy('id');
        $cleanCart = [];
        $items = collect();
        $subtotal = 0;
        $codes = [];

        foreach ($cart as $productId => $quantity) {
            $product = $products->get((int) $productId);
            if (! $product || $product->stock < 1) continue;

            $quantity = max(1, min((int) $quantity, $product->stock));
            $cleanCart[$product->id] = $quantity;
            $lineTotal = $product->price * $quantity;
            $subtotal += $lineTotal;
            $codes[] = $product->code;
            $items->push(compact('product', 'quantity', 'lineTotal'));
        }

        $request->session()->put('cart', $cleanCart);
        return [$items, $subtotal, $codes];
    }
}

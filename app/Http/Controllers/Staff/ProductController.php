<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim((string) $request->input('q'));
                $query->where(fn ($sub) => $sub->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
            })
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('staff.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('staff.products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        Product::query()->create($data);
        return redirect()->route('staff.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('staff.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product);
        unset($data['code']); // Kode produk dibuat stabil agar riwayat Apriori tidak rusak.
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($product->image_path) Storage::disk('public')->delete($product->image_path);
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $product->update($data);
        return redirect()->route('staff.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('staff.products.index')->with('success', 'Produk dinonaktifkan dari katalog melalui soft delete.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'code' => [
                'required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('products', 'code')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AprioriService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request, AprioriService $apriori): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = trim((string) $request->input('q'));
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->paginate(12)
            ->withQueryString();

        $cart = session('cart', []);
        $cartCodes = Product::query()->whereIn('id', array_keys($cart))->pluck('code')->all();
        $recommendations = $apriori->recommendationsForCodes($cartCodes);
        $popularRules = $apriori->topRuleSummaries(4);
        $latestRun = $apriori->latestSuccessfulRun();

        return view('storefront.index', compact('products', 'recommendations', 'popularRules', 'latestRun'));
    }
}

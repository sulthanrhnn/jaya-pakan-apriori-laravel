<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AprioriRun;
use App\Models\Product;
use App\Services\AprioriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class AprioriController extends Controller
{
    public function index(AprioriService $service): View
    {
        $latestRun = $service->latestSuccessfulRun();
        $productNames = Product::withTrashed()->pluck('name', 'code');
        $itemsets = $latestRun ? $latestRun->itemsets()->orderBy('itemset_size')->orderByDesc('support')->get() : collect();
        $rules = $latestRun ? $latestRun->rules()->orderByDesc('confidence')->orderByDesc('lift')->get() : collect();
        $runs = AprioriRun::query()->latest('id')->limit(10)->get();

        return view('staff.apriori.index', compact('latestRun', 'productNames', 'itemsets', 'rules', 'runs'));
    }

    public function run(AprioriService $service): RedirectResponse
    {
        try {
            $run = $service->generate();
            return back()->with('success', "Apriori selesai: {$run->eligible_rule_count} aturan layak dari {$run->transaction_count} transaksi selesai.");
        } catch (Throwable $exception) {
            report($exception);
            return back()->with('error', 'Proses Apriori gagal: '.$exception->getMessage());
        }
    }
}

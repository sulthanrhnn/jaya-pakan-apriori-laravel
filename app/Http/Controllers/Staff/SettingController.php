<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AprioriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = Setting::query()->pluck('value', 'key');
        return view('staff.settings.edit', compact('settings'));
    }

    public function update(Request $request, AprioriService $apriori): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:180'],
            'bank_name' => ['required', 'string', 'max:80'],
            'bank_account_number' => ['required', 'string', 'max:80'],
            'bank_account_name' => ['required', 'string', 'max:180'],
            'apriori_min_support' => ['required', 'numeric', 'gt:0', 'lte:1'],
            'apriori_min_confidence' => ['required', 'numeric', 'gt:0', 'lte:1'],
            'apriori_min_lift' => ['required', 'numeric', 'gte:1'],
            'apriori_max_itemset' => ['required', 'integer', 'min:2', 'max:5'],
        ]);

        $types = [
            'apriori_min_support' => 'float',
            'apriori_min_confidence' => 'float',
            'apriori_min_lift' => 'float',
            'apriori_max_itemset' => 'integer',
        ];

        foreach ($data as $key => $value) {
            Setting::put($key, $value, $types[$key] ?? 'string');
        }

        try {
            $apriori->generate();
        } catch (Throwable $exception) {
            report($exception);
            return back()->with('error', 'Pengaturan tersimpan, tetapi kalkulasi Apriori ulang gagal: '.$exception->getMessage());
        }

        return back()->with('success', 'Pengaturan berhasil disimpan dan aturan Apriori telah dihitung ulang.');
    }
}

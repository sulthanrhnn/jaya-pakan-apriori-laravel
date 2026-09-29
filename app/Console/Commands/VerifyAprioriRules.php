<?php

namespace App\Console\Commands;

use App\Models\AprioriRule;
use App\Services\AprioriService;
use Illuminate\Console\Command;

class VerifyAprioriRules extends Command
{
    protected $signature = 'apriori:verify';
    protected $description = 'Memverifikasi hasil Apriori database terhadap hasil yang tercatat pada workbook penelitian.';

    public function handle(AprioriService $service): int
    {
        $run = $service->latestSuccessfulRun();

        if (! $run) {
            $this->error('Belum ada proses Apriori yang berhasil. Jalankan php artisan apriori:generate.');
            return self::FAILURE;
        }

        $expected = json_decode((string) file_get_contents(database_path('data/expected_apriori_rules.json')), true);
        $actual = AprioriRule::query()->where('apriori_run_id', $run->id)->get();

        $failures = [];
        if ($run->transaction_count !== 300) $failures[] = "Jumlah transaksi {$run->transaction_count}, seharusnya 300 pada dataset awal.";
        if ($run->frequent_1_count !== 8) $failures[] = "Frequent 1-itemset {$run->frequent_1_count}, seharusnya 8.";
        if ($run->frequent_2_count !== 8) $failures[] = "Frequent 2-itemset {$run->frequent_2_count}, seharusnya 8.";
        if ($run->frequent_3_count !== 1) $failures[] = "Frequent 3-itemset {$run->frequent_3_count}, seharusnya 1.";
        if ($run->eligible_rule_count !== 17) $failures[] = "Aturan layak {$run->eligible_rule_count}, seharusnya 17.";

        $canonical = fn (array $codes): string => collect($codes)->sort(fn ($a, $b) => strnatcasecmp($a, $b))->implode('|');
        $actualByKey = $actual->keyBy(fn (AprioriRule $rule) => $canonical($rule->antecedent_codes).'->'.$canonical($rule->consequent_codes));

        foreach ($expected as $row) {
            $key = $canonical($row['antecedent_codes']).'->'.$canonical($row['consequent_codes']);
            $rule = $actualByKey->get($key);
            if (! $rule) {
                $failures[] = "Aturan {$key} tidak ditemukan.";
                continue;
            }

            foreach (['support', 'confidence', 'lift'] as $metric) {
                if (abs((float) $rule->{$metric} - (float) $row[$metric]) > 0.000001) {
                    $failures[] = "{$key}: {$metric} berbeda.";
                }
            }
        }

        if ($failures !== []) {
            foreach ($failures as $failure) $this->error($failure);
            return self::FAILURE;
        }

        $this->info('VERIFIKASI BERHASIL: 300 transaksi, F1=8, F2=8, F3=1, dan 17 aturan layak sesuai workbook sumber.');
        return self::SUCCESS;
    }
}

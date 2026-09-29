<?php

namespace App\Console\Commands;

use App\Services\AprioriService;
use Illuminate\Console\Command;
use Throwable;

class GenerateAprioriRules extends Command
{
    protected $signature = 'apriori:generate
        {--support= : Minimum support, contoh 0.20}
        {--confidence= : Minimum confidence, contoh 0.60}
        {--lift= : Minimum lift, contoh 1.0}
        {--max-itemset= : Maksimum panjang itemset, contoh 3}';

    protected $description = 'Menghitung frequent itemset dan aturan asosiasi Apriori dari transaksi selesai.';

    public function handle(AprioriService $service): int
    {
        try {
            $run = $service->generate(
                $this->option('support') !== null ? (float) $this->option('support') : null,
                $this->option('confidence') !== null ? (float) $this->option('confidence') : null,
                $this->option('lift') !== null ? (float) $this->option('lift') : null,
                $this->option('max-itemset') !== null ? (int) $this->option('max-itemset') : null,
            );

            $this->info('Apriori berhasil diproses.');
            $this->table(
                ['Transaksi', 'F1', 'F2', 'F3', 'Aturan Layak', 'Durasi'],
                [[
                    $run->transaction_count,
                    $run->frequent_1_count,
                    $run->frequent_2_count,
                    $run->frequent_3_count,
                    $run->eligible_rule_count,
                    $run->duration_ms.' ms',
                ]]
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}

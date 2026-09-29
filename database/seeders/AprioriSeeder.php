<?php

namespace Database\Seeders;

use App\Services\AprioriService;
use Illuminate\Database\Seeder;
use RuntimeException;

class AprioriSeeder extends Seeder
{
    public function run(): void
    {
        /** @var AprioriService $service */
        $service = app(AprioriService::class);
        $run = $service->generate(0.20, 0.60, 1.0, 3);

        if ($run->transaction_count !== 300 || $run->frequent_1_count !== 8 || $run->frequent_2_count !== 8 || $run->frequent_3_count !== 1 || $run->eligible_rule_count !== 17) {
            throw new RuntimeException(sprintf(
                'Hasil Apriori tidak sesuai dataset sumber. Diperoleh: transaksi=%d, F1=%d, F2=%d, F3=%d, rules=%d.',
                $run->transaction_count,
                $run->frequent_1_count,
                $run->frequent_2_count,
                $run->frequent_3_count,
                $run->eligible_rule_count
            ));
        }
    }
}

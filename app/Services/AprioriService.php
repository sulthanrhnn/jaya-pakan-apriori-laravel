<?php

namespace App\Services;

use App\Models\AprioriItemset;
use App\Models\AprioriRule;
use App\Models\AprioriRun;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class AprioriService
{
    public function generate(?float $minimumSupport = null, ?float $minimumConfidence = null, ?float $minimumLift = null, ?int $maxItemsetSize = null): AprioriRun
    {
        $startedAt = microtime(true);
        $minimumSupport ??= (float) Setting::valueOf('apriori_min_support', 0.20);
        $minimumConfidence ??= (float) Setting::valueOf('apriori_min_confidence', 0.60);
        $minimumLift ??= (float) Setting::valueOf('apriori_min_lift', 1.0);
        $maxItemsetSize ??= (int) Setting::valueOf('apriori_max_itemset', 3);

        $baskets = Order::query()
            ->where('status', 'completed')
            ->with('items:id,order_id,product_code')
            ->orderBy('id')
            ->get()
            ->map(function (Order $order): array {
                return $this->sortCodes($order->items->pluck('product_code')->filter()->unique()->values()->all());
            })
            ->filter()
            ->values()
            ->all();

        $totalTransactions = count($baskets);

        if ($totalTransactions === 0) {
            throw new RuntimeException('Tidak ada transaksi berstatus selesai yang dapat diproses oleh Apriori.');
        }

        $run = AprioriRun::query()->create([
            'transaction_count' => $totalTransactions,
            'minimum_support' => $minimumSupport,
            'minimum_confidence' => $minimumConfidence,
            'minimum_lift' => $minimumLift,
            'max_itemset_size' => $maxItemsetSize,
            'status' => 'running',
        ]);

        try {
            [$frequentBySize, $countsByKey] = $this->buildFrequentItemsets(
                $baskets,
                $minimumSupport,
                $maxItemsetSize
            );

            $rules = $this->buildRules(
                $baskets,
                $frequentBySize,
                $countsByKey,
                $minimumSupport,
                $minimumConfidence,
                $minimumLift
            );

            DB::transaction(function () use ($run, $frequentBySize, $rules, $totalTransactions, $minimumSupport, $minimumConfidence, $minimumLift, $maxItemsetSize, $startedAt): void {
                foreach ($frequentBySize as $size => $itemsets) {
                    foreach ($itemsets as $item) {
                        AprioriItemset::query()->create([
                            'apriori_run_id' => $run->id,
                            'itemset_size' => $size,
                            'item_codes' => $item['codes'],
                            'transaction_count' => $item['count'],
                            'support' => $item['support'],
                        ]);
                    }
                }

                foreach ($rules as $rule) {
                    AprioriRule::query()->create([
                        'apriori_run_id' => $run->id,
                        ...$rule,
                    ]);
                }

                $run->update([
                    'transaction_count' => $totalTransactions,
                    'minimum_support' => $minimumSupport,
                    'minimum_confidence' => $minimumConfidence,
                    'minimum_lift' => $minimumLift,
                    'max_itemset_size' => $maxItemsetSize,
                    'frequent_1_count' => count($frequentBySize[1] ?? []),
                    'frequent_2_count' => count($frequentBySize[2] ?? []),
                    'frequent_3_count' => count($frequentBySize[3] ?? []),
                    'eligible_rule_count' => count($rules),
                    'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'status' => 'success',
                    'message' => 'Apriori berhasil diproses dari transaksi selesai.',
                ]);
            });

            return $run->fresh(['itemsets', 'rules']);
        } catch (Throwable $exception) {
            $run->update([
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * @return array{0: array<int, array<int, array{codes: array<int,string>, count:int, support:float}>>, 1: array<string,int>}
     */
    private function buildFrequentItemsets(array $baskets, float $minimumSupport, int $maxItemsetSize): array
    {
        $total = count($baskets);
        $allCodes = $this->sortCodes(array_values(array_unique(array_merge(...$baskets))));
        $frequentBySize = [];
        $countsByKey = [];

        $candidates = array_map(fn (string $code): array => [$code], $allCodes);

        for ($size = 1; $size <= $maxItemsetSize && $candidates !== []; $size++) {
            $frequent = [];

            foreach ($candidates as $candidate) {
                $count = $this->countContaining($baskets, $candidate);
                $countsByKey[$this->key($candidate)] = $count;
                $support = $count / $total;

                if ($support + 1e-12 >= $minimumSupport) {
                    $frequent[] = [
                        'codes' => $candidate,
                        'count' => $count,
                        'support' => $support,
                    ];
                }
            }

            $frequentBySize[$size] = $frequent;

            if ($size >= $maxItemsetSize || $frequent === []) {
                break;
            }

            $frequentCodes = [];
            foreach ($frequent as $item) {
                foreach ($item['codes'] as $code) {
                    $frequentCodes[$code] = true;
                }
            }

            $nextSize = $size + 1;
            $candidatePool = $this->combinations($this->sortCodes(array_keys($frequentCodes)), $nextSize);
            $previousKeys = array_fill_keys(array_map(fn (array $item): string => $this->key($item['codes']), $frequent), true);

            $candidates = array_values(array_filter($candidatePool, function (array $candidate) use ($previousKeys, $size): bool {
                foreach ($this->combinations($candidate, $size) as $subset) {
                    if (! isset($previousKeys[$this->key($subset)])) {
                        return false;
                    }
                }

                return true;
            }));
        }

        return [$frequentBySize, $countsByKey];
    }

    private function buildRules(array $baskets, array $frequentBySize, array $countsByKey, float $minimumSupport, float $minimumConfidence, float $minimumLift): array
    {
        $total = count($baskets);
        $rules = [];

        foreach ($frequentBySize as $size => $itemsets) {
            if ($size < 2) {
                continue;
            }

            foreach ($itemsets as $itemset) {
                $unionCodes = $itemset['codes'];
                $unionCount = $itemset['count'];
                $support = $itemset['support'];

                for ($antecedentSize = 1; $antecedentSize < $size; $antecedentSize++) {
                    foreach ($this->combinations($unionCodes, $antecedentSize) as $antecedent) {
                        $consequent = array_values(array_diff($unionCodes, $antecedent));
                        $consequent = $this->sortCodes($consequent);
                        $countAntecedent = $countsByKey[$this->key($antecedent)] ?? $this->countContaining($baskets, $antecedent);
                        $countConsequent = $countsByKey[$this->key($consequent)] ?? $this->countContaining($baskets, $consequent);

                        if ($countAntecedent === 0 || $countConsequent === 0) {
                            continue;
                        }

                        $confidence = $unionCount / $countAntecedent;
                        $supportConsequent = $countConsequent / $total;
                        $lift = $supportConsequent > 0 ? $confidence / $supportConsequent : 0.0;

                        if ($support + 1e-12 < $minimumSupport || $confidence + 1e-12 < $minimumConfidence || $lift <= $minimumLift) {
                            continue;
                        }

                        $rules[] = [
                            'antecedent_codes' => $antecedent,
                            'consequent_codes' => $consequent,
                            'count_antecedent' => $countAntecedent,
                            'count_consequent' => $countConsequent,
                            'count_union' => $unionCount,
                            'support' => $support,
                            'confidence' => $confidence,
                            'support_consequent' => $supportConsequent,
                            'lift' => $lift,
                        ];
                    }
                }
            }
        }

        usort($rules, fn (array $a, array $b): int => [$b['confidence'], $b['lift']] <=> [$a['confidence'], $a['lift']]);

        return $rules;
    }

    public function latestSuccessfulRun(): ?AprioriRun
    {
        return AprioriRun::query()->where('status', 'success')->latest('id')->first();
    }

    public function recommendationsForCodes(array $cartCodes, int $limit = 6): Collection
    {
        $cartCodes = $this->sortCodes(array_values(array_unique($cartCodes)));
        $run = $this->latestSuccessfulRun();

        if (! $run || $cartCodes === []) {
            return collect();
        }

        $products = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->get()
            ->keyBy('code');

        $bestByProduct = [];

        $rules = AprioriRule::query()
            ->where('apriori_run_id', $run->id)
            ->orderByDesc('confidence')
            ->orderByDesc('lift')
            ->get();

        foreach ($rules as $rule) {
            if (! $this->isSubset($rule->antecedent_codes, $cartCodes)) {
                continue;
            }

            foreach ($rule->consequent_codes as $code) {
                if (in_array($code, $cartCodes, true) || ! $products->has($code)) {
                    continue;
                }

                $candidate = [
                    'product' => $products->get($code),
                    'rule' => $rule,
                    'confidence' => (float) $rule->confidence,
                    'lift' => (float) $rule->lift,
                ];

                if (! isset($bestByProduct[$code]) || $this->isBetterRecommendation($candidate, $bestByProduct[$code])) {
                    $bestByProduct[$code] = $candidate;
                }
            }
        }

        return collect(array_values($bestByProduct))
            ->sortByDesc(fn (array $item): string => sprintf('%020.10f-%020.10f', $item['confidence'], $item['lift']))
            ->take($limit)
            ->values();
    }

    public function topRuleSummaries(int $limit = 6): Collection
    {
        $run = $this->latestSuccessfulRun();

        if (! $run) {
            return collect();
        }

        $productNames = Product::withTrashed()->get()->pluck('name', 'code');

        return AprioriRule::query()
            ->where('apriori_run_id', $run->id)
            ->orderByDesc('confidence')
            ->orderByDesc('lift')
            ->limit($limit)
            ->get()
            ->map(function (AprioriRule $rule) use ($productNames): array {
                return [
                    'rule' => $rule,
                    'antecedent_names' => collect($rule->antecedent_codes)->map(fn (string $code) => $productNames[$code] ?? $code)->all(),
                    'consequent_names' => collect($rule->consequent_codes)->map(fn (string $code) => $productNames[$code] ?? $code)->all(),
                ];
            });
    }

    private function isBetterRecommendation(array $candidate, array $current): bool
    {
        if (abs($candidate['confidence'] - $current['confidence']) > 1e-12) {
            return $candidate['confidence'] > $current['confidence'];
        }

        return $candidate['lift'] > $current['lift'];
    }

    private function isSubset(array $subset, array $set): bool
    {
        return count(array_diff($subset, $set)) === 0;
    }

    private function countContaining(array $baskets, array $candidate): int
    {
        $count = 0;

        foreach ($baskets as $basket) {
            if ($this->isSubset($candidate, $basket)) {
                $count++;
            }
        }

        return $count;
    }

    /** @return array<int, array<int, string>> */
    private function combinations(array $items, int $size): array
    {
        $items = array_values($items);
        $result = [];
        $count = count($items);

        if ($size < 1 || $size > $count) {
            return [];
        }

        $walk = function (int $start, array $current) use (&$walk, &$result, $items, $count, $size): void {
            if (count($current) === $size) {
                $result[] = $this->sortCodes($current);
                return;
            }

            $needed = $size - count($current);
            for ($i = $start; $i <= $count - $needed; $i++) {
                $next = $current;
                $next[] = $items[$i];
                $walk($i + 1, $next);
            }
        };

        $walk(0, []);

        return $result;
    }

    private function key(array $codes): string
    {
        return implode('|', $this->sortCodes($codes));
    }

    private function sortCodes(array $codes): array
    {
        $codes = array_values(array_unique($codes));
        usort($codes, fn (string $a, string $b): int => strnatcasecmp($a, $b));

        return $codes;
    }
}

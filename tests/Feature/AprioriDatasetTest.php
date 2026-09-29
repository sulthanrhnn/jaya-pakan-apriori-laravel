<?php

namespace Tests\Feature;

use App\Models\AprioriRun;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AprioriDatasetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dataset_and_apriori_match_source_workbook(): void
    {
        $this->seed();

        $this->assertSame(15, Product::query()->count());
        $this->assertSame(300, Order::query()->where('source', 'historical')->count());
        $this->assertSame(841, OrderItem::query()->whereHas('order', fn ($q) => $q->where('source', 'historical'))->count());

        $run = AprioriRun::query()->where('status', 'success')->latest('id')->firstOrFail();
        $this->assertSame(300, $run->transaction_count);
        $this->assertSame(8, $run->frequent_1_count);
        $this->assertSame(8, $run->frequent_2_count);
        $this->assertSame(1, $run->frequent_3_count);
        $this->assertSame(17, $run->eligible_rule_count);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/products.json');
        $products = json_decode((string) file_get_contents($path), true);

        if (! is_array($products) || count($products) !== 15) {
            throw new RuntimeException('Data products.json tidak valid atau tidak berjumlah 15 produk.');
        }

        foreach ($products as $row) {
            $product = Product::withTrashed()->where('code', $row['code'])->first();

            if ($product) {
                if ($product->trashed()) {
                    $product->restore();
                }
                $product->update([
                    'name' => $row['name'],
                    'price' => (int) $row['price'],
                    'is_active' => true,
                ]);
                continue;
            }

            Product::query()->create([
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => null,
                'price' => (int) $row['price'],
                // Workbook sumber tidak memuat stok berjalan. Nilai 100 hanya stok demo instalasi awal.
                'stock' => 100,
                'is_active' => true,
            ]);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorefrontFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_displays_apriori_information(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Algoritma Apriori')
            ->assertSee('300');
    }

    public function test_cart_recommends_jagung_after_p1_and_p7_are_selected(): void
    {
        $p1 = Product::query()->where('code', 'P1')->firstOrFail();
        $p7 = Product::query()->where('code', 'P7')->firstOrFail();

        $this->withSession(['cart' => [$p1->id => 1, $p7->id => 1]])
            ->get('/keranjang')
            ->assertOk()
            ->assertSee('Jagung Giling')
            ->assertSee('Confidence');
    }

    public function test_customer_can_checkout_without_account_and_receive_invoice(): void
    {
        Storage::fake('local');
        $product = Product::query()->where('code', 'P1')->firstOrFail();

        $response = $this->withSession(['cart' => [$product->id => 1]])->post('/checkout', [
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jalan Pengujian Nomor 10, Pekanbaru',
            'customer_note' => 'Uji checkout',
            'payment_proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ]);

        $order = Order::query()->where('source', 'web')->latest('id')->firstOrFail();
        $response->assertRedirect(route('orders.receipt', $order->invoice_number));
        $this->assertSame('pending_verification', $order->status);
        $this->assertStringStartsWith('JPU-', $order->invoice_number);
        Storage::disk('local')->assertExists($order->payment_proof_path);
    }
}

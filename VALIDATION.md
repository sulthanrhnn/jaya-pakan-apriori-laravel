# Validation Report

Pemeriksaan yang dilakukan saat paket ini dibangun:

1. **PHP syntax lint:** seluruh file PHP aplikasi, migration, seeder, route, command, test, dan tool lolos `php -l`.
2. **Dataset source:** 15 produk, 300 transaksi, dan 841 detail transaksi tervalidasi.
3. **Nilai transaksi:** jumlah subtotal detail untuk setiap invoice sama dengan total transaksi sumber.
4. **Apriori standalone:** F1=8, F2=8, F3=1, aturan layak=17.
5. **AprioriService:** fungsi perhitungan inti pada `App\Services\AprioriService` diuji langsung terhadap 300 basket transaksi dan menghasilkan 8/8/1/17.
6. **Metrik aturan:** 17 nilai support, confidence, dan lift dari service cocok dengan `expected_apriori_rules.json`.
7. **Referensi route Blade:** seluruh pemanggilan `route(...)` pada view memiliki route yang sesuai.
8. **Blade control directives:** pasangan `@if/@endif`, `@foreach/@endforeach`, `@forelse/@endforelse`, dan `@auth/@endauth` seimbang.

## Batasan lingkungan build

Environment pembuatan paket memiliki PHP tetapi tidak menyediakan Composer/vendor Laravel, sehingga full `php artisan test` tidak dapat dieksekusi di environment build. Test Laravel sudah disertakan pada folder `tests/` dan dapat dijalankan setelah `composer install` pada komputer lokal:

```bash
php artisan test
```

Untuk verifikasi dataset tanpa Composer/vendor Laravel:

```bash
php tools/verify_source.php
```

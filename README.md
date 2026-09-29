# Sistem Pemesanan CV. Jaya Pakan Utama + Algoritma Apriori

Aplikasi ini dibuat berbasis **Laravel 12 + MySQL** untuk menyesuaikan proposal:

> **“Pengembangan Sistem Pemesanan Produk Berbasis Web dengan Penerapan Algoritma Apriori untuk Rekomendasi Prediktif Pembelian.”**

Aplikasi sudah memuat **15 produk, 300 transaksi historis, dan 841 baris detail transaksi** yang disalin dari workbook penelitian. Saat instalasi awal, seeder menghitung ulang Apriori dan memverifikasi hasil dasar: **8 frequent 1-itemset, 8 frequent 2-itemset, 1 frequent 3-itemset, dan 17 aturan asosiasi layak**.

---

## 1. Fitur Utama

### Halaman pelanggan / pemesan — tanpa akun
- Beranda dan katalog produk.
- Informasi implementasi Apriori di beranda.
- Keranjang berbasis session.
- Rekomendasi produk berdasarkan antecedent yang sudah ada di keranjang.
- Rekomendasi diurutkan berdasarkan **confidence**, lalu **lift**.
- Produk yang sudah ada di keranjang tidak direkomendasikan ulang.
- Produk tanpa stok tidak masuk rekomendasi.
- Checkout tanpa registrasi akun.
- Pembayaran manual melalui:
  - **Bank:** BCA
  - **No. Rekening:** `123456789`
  - **Atas Nama:** `CV. JAYA PAKAN UTAMA`
- Upload bukti transfer JPG/JPEG/PNG/PDF maksimal 5 MB.
- Nomor invoice otomatis dengan format `JPU-YYYYMMDD-XXXXXX`.
- Struk belanja setelah checkout.
- Tombol cetak / simpan struk sebagai PDF melalui fitur print browser.
- Menu **Cek Pesanan Anda** menggunakan nomor invoice.
- Timeline status pesanan.

### Role Kasir
Kasir dapat:
- Login staff.
- Melihat dashboard.
- Melihat pesanan web dan transaksi historis.
- Memverifikasi pembayaran.
- Mengubah status pesanan.
- Melihat bukti transfer yang disimpan secara privat.
- CRUD produk dan stok.

Kasir **tidak** dapat mengelola akun staff, parameter Apriori, pengaturan sistem, atau laporan admin.

### Role Admin
Admin memiliki seluruh akses kasir ditambah:
- CRUD akun admin/kasir.
- Menjalankan ulang Algoritma Apriori.
- Melihat frequent itemset dan aturan asosiasi.
- Melihat riwayat proses Apriori.
- Mengubah rekening pembayaran.
- Mengubah minimum support, confidence, lift, dan maksimum itemset.
- Melihat laporan transaksi dan produk terlaris.

---

## 2. Ketentuan Apriori yang Digunakan

Konfigurasi awal sesuai proposal dan workbook:

| Parameter | Nilai |
|---|---:|
| Minimum Support | 0.20 / 20% |
| Minimum Confidence | 0.60 / 60% |
| Lift | > 1 |
| Maksimum itemset | 3 |
| Dataset awal | 300 transaksi selesai |
| Produk | 15 |
| Frequent 1-itemset | 8 |
| Frequent 2-itemset | 8 |
| Frequent 3-itemset | 1 |
| Aturan layak | 17 |

Apriori hanya menggunakan pesanan dengan status **completed / selesai**. Ketika pesanan web baru diubah kasir menjadi **Selesai**, sistem otomatis menghitung ulang aturan Apriori agar transaksi baru dapat ikut memengaruhi rekomendasi berikutnya.

### Cara rekomendasi bekerja
1. Sistem membaca kode produk di keranjang.
2. Sistem mengambil aturan Apriori dari proses terakhir yang berhasil.
3. Seluruh item pada antecedent harus sudah terdapat di keranjang.
4. Consequent yang sudah berada di keranjang dihapus dari kandidat rekomendasi.
5. Hanya produk aktif dengan stok > 0 yang ditampilkan.
6. Bila satu produk muncul dari lebih dari satu aturan, dipilih aturan dengan confidence tertinggi lalu lift tertinggi.

---

## 3. Data yang Sudah Disertakan

Folder `database/data/` berisi hasil konversi langsung dari workbook sumber:

- `products.json` — 15 produk P1 sampai P15 beserta harga.
- `transactions.json` — 300 transaksi periode Juli 2025 sampai Juni 2026.
- `transaction_items.json` — 841 detail item transaksi.
- `expected_frequent_itemsets.json` — hasil frequent itemset dari workbook.
- `expected_apriori_rules.json` — 17 aturan layak dari workbook.
- `source_manifest.json` — parameter dan metadata dataset.

### Catatan stok awal
Workbook penelitian **tidak memuat angka stok berjalan saat ini**. Agar aplikasi dapat langsung diuji setelah instalasi, seeder memberi **stok demo awal 100 unit untuk setiap produk**. Sebelum penggunaan nyata, ubah stok melalui menu **Produk & Stok** sesuai stok fisik perusahaan.

Transaksi historis tidak mengurangi stok demo karena transaksi tersebut merupakan dataset penelitian masa lalu.

---

# 4. Persyaratan Lokal

Disarankan:

- PHP **8.2 atau lebih baru**.
- Composer 2.x.
- MySQL 8.x / MariaDB yang kompatibel.
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `json`, `tokenizer`, `xml`.
- Apache / Nginx, atau cukup `php artisan serve` untuk pengembangan lokal.

Laravel dapat dijalankan menggunakan **Laragon**, **XAMPP**, terminal PHP lokal, atau stack sejenis.

---

# 5. Instalasi ke Database Lokal

## A. Ekstrak project

Ekstrak ZIP ke folder kerja, misalnya:

```text
C:\laragon\www\jaya-pakan-apriori-laravel
```

atau:

```text
C:\xampp\htdocs\jaya-pakan-apriori-laravel
```

Masuk ke folder project melalui terminal.

```bash
cd jaya-pakan-apriori-laravel
```

## B. Install dependency Laravel

```bash
composer install
```

Project ZIP sengaja tidak menyertakan folder `vendor` agar ukuran paket lebih kecil dan dependency diinstal sesuai environment komputer Anda.

## C. Buat database MySQL

Bisa melalui phpMyAdmin atau terminal MySQL.

```sql
CREATE DATABASE jaya_pakan_utama
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

## D. Buat file `.env`

Windows Command Prompt:

```bat
copy .env.example .env
```

PowerShell / Linux / macOS:

```bash
cp .env.example .env
```

Lalu edit bagian database pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jaya_pakan_utama
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan MySQL lokal Anda.

## E. Generate application key

```bash
php artisan key:generate
```

## F. Buat symbolic link untuk gambar produk

```bash
php artisan storage:link
```

Bukti transfer **tidak** diletakkan di folder public. Bukti transfer disimpan pada storage private dan hanya dapat diakses oleh staff yang login.

## G. Migrasi dan import seluruh data penelitian

Pada database baru, jalankan:

```bash
php artisan migrate:fresh --seed
```

Perintah ini akan:

1. Membuat tabel aplikasi.
2. Membuat akun admin dan kasir awal.
3. Mengimpor 15 produk.
4. Mengimpor 300 transaksi historis.
5. Mengimpor 841 baris detail transaksi.
6. Menyimpan rekening pembayaran BCA.
7. Menjalankan Apriori.
8. Memastikan hasil awal = F1 8, F2 8, F3 1, dan 17 aturan layak.

> **Peringatan:** `migrate:fresh` menghapus seluruh tabel pada database yang dipilih. Gunakan hanya pada database baru/development. Untuk server yang sudah berisi transaksi nyata, gunakan `php artisan migrate` tanpa `fresh`.

## H. Verifikasi dataset dan hasil Apriori

Sebelum Laravel pun dapat dijalankan, dataset JSON dapat diperiksa dengan:

```bash
php tools/verify_source.php
```

Output yang benar:

```text
[OK] Dataset valid: 15 produk, 300 transaksi, 841 detail.
[OK] Apriori: F1=8, F2=8, F3=1, aturan layak=17.
[OK] Seluruh support/confidence/lift cocok dengan workbook sumber.
```

Setelah database selesai di-seed:

```bash
php artisan apriori:verify
```

Verifikasi ini ditujukan untuk kondisi dataset awal 300 transaksi. Setelah pesanan web baru selesai dan ikut dianalisis, jumlah transaksi maupun aturan dapat berubah secara wajar.

## I. Jalankan aplikasi

```bash
php artisan serve
```

Buka:

```text
http://127.0.0.1:8000
```

Login staff:

```text
http://127.0.0.1:8000/login
```

---

# 6. Akun Default

### Admin
```text
Email    : admin@jayapakan.test
Password : Admin123!
```

### Kasir
```text
Email    : kasir@jayapakan.test
Password : Kasir123!
```

**Ganti password akun default sebelum dipakai di server produksi.**

---

# 7. Alur Pemesanan Pelanggan

1. Pelanggan membuka beranda.
2. Pelanggan melihat katalog produk dan informasi Apriori.
3. Pelanggan memasukkan produk ke keranjang.
4. Sistem mencocokkan isi keranjang dengan antecedent aturan Apriori.
5. Sistem menampilkan produk rekomendasi yang relevan.
6. Pelanggan melanjutkan checkout.
7. Pelanggan mengisi nama, nomor telepon, alamat, dan catatan opsional.
8. Pelanggan transfer ke BCA `123456789` a.n. `CV. JAYA PAKAN UTAMA`.
9. Pelanggan mengunggah bukti transfer.
10. Sistem membuat order, mengurangi stok sebagai reservasi, dan menerbitkan nomor invoice.
11. Pelanggan melihat/mencetak struk.
12. Kasir memverifikasi pembayaran dan memperbarui status.
13. Pelanggan membuka menu **Cek Pesanan Anda**, memasukkan invoice, lalu melihat status dan timeline.
14. Jika pesanan dibatalkan, stok otomatis dikembalikan.
15. Jika pesanan menjadi **Selesai**, sistem menghitung ulang Apriori.

---

# 8. Status Pesanan

Status yang tersedia:

- `pending_verification` — Menunggu Verifikasi
- `processing` — Diproses
- `ready` — Siap Dikirim/Diambil
- `shipped` — Dikirim
- `completed` — Selesai
- `cancelled` — Dibatalkan

Status pembayaran:

- `pending` — Menunggu Verifikasi
- `verified` — Terverifikasi
- `rejected` — Ditolak

Sistem tidak mengizinkan order masuk ke tahap proses/siap/dikirim/selesai jika pembayaran belum berstatus **verified**.

---

# 9. Perintah Apriori

Jalankan dengan parameter dari pengaturan database:

```bash
php artisan apriori:generate
```

Atau parameter khusus:

```bash
php artisan apriori:generate --support=0.20 --confidence=0.60 --lift=1 --max-itemset=3
```

Verifikasi baseline:

```bash
php artisan apriori:verify
```

Hasil Apriori juga dapat diproses melalui dashboard admin: **Algoritma Apriori → Proses Ulang Apriori**.

---

# 10. Struktur Database Penting

- `users` — akun admin/kasir.
- `products` — katalog, harga, stok, gambar, status aktif.
- `orders` — transaksi historis dan pesanan web.
- `order_items` — detail produk per transaksi.
- `order_status_logs` — timeline perubahan status pesanan web.
- `apriori_runs` — metadata setiap proses Apriori.
- `apriori_itemsets` — frequent itemset hasil proses.
- `apriori_rules` — aturan asosiasi yang memenuhi threshold.
- `settings` — rekening dan parameter sistem.

Kolom `orders.source` membedakan:

- `historical` = 300 transaksi penelitian yang diimpor dari workbook.
- `web` = transaksi baru dari pelanggan melalui aplikasi.

Dataset `historical` dibuat read-only dari dashboard agar data sumber penelitian tidak berubah secara tidak sengaja.

---

# 11. CRUD yang Tersedia

### Produk
- Create produk baru.
- Read daftar produk.
- Update nama, harga, stok, deskripsi, gambar, dan status aktif.
- Delete memakai **soft delete**, sehingga riwayat transaksi tetap aman.
- Kode produk lama dikunci saat edit karena kode dipakai sebagai identitas dalam transaksi dan Apriori.

### Staff
- Admin dapat create/read/update/delete akun admin atau kasir.
- Admin tidak dapat menghapus akun yang sedang digunakannya.
- Sistem menjaga agar admin aktif tetap tersedia.

### Pesanan
Pesanan pelanggan dibuat dari checkout, lalu kasir/admin mengelola pembayaran dan status. Data historis tampil untuk analisis dan laporan, tetapi tidak boleh diubah.

---

# 12. Pengujian

Jika dependency sudah terpasang:

```bash
php artisan test
```

Test yang disertakan memeriksa:

- 15 produk berhasil diimpor.
- 300 transaksi historis berhasil diimpor.
- 841 detail transaksi berhasil diimpor.
- Apriori menghasilkan F1=8, F2=8, F3=1, dan 17 aturan layak.
- Beranda menampilkan informasi Apriori.
- Keranjang P1 + P7 merekomendasikan Jagung Giling.
- Pelanggan dapat checkout tanpa akun dan memperoleh invoice.
- Bukti pembayaran tersimpan di storage private.

Pemeriksaan syntax PHP tanpa menjalankan Laravel:

```bash
find app bootstrap config database routes public -name "*.php" -print0 | xargs -0 -n1 php -l
```

Pada Windows, pemeriksaan syntax dapat dilakukan per file atau melalui terminal Git Bash/WSL.

---

# 13. Troubleshooting

### `composer` tidak dikenali
Install Composer dari situs resmi Composer, tutup dan buka kembali terminal, lalu cek:

```bash
composer --version
```

### `could not find driver`
Aktifkan extension `pdo_mysql` pada `php.ini`, lalu restart Apache/PHP.

### `Access denied for user ...`
Periksa `DB_USERNAME` dan `DB_PASSWORD` pada `.env`.

### `Unknown database jaya_pakan_utama`
Buat database terlebih dahulu melalui MySQL/phpMyAdmin.

### Gambar produk tidak muncul
Jalankan:

```bash
php artisan storage:link
```

### Perubahan `.env` belum terbaca

```bash
php artisan optimize:clear
```

### Class baru tidak ditemukan

```bash
composer dump-autoload
php artisan optimize:clear
```

### Ingin reset development dari awal

```bash
php artisan migrate:fresh --seed
```

Jangan menjalankan `migrate:fresh` pada database produksi yang sudah berisi transaksi nyata.

---

# 14. Deploy ke Hosting

Untuk Apache/Nginx, document root harus diarahkan ke folder:

```text
/project/public
```

Bukan ke root project.

Sebelum produksi:

```env
APP_ENV=production
APP_DEBUG=false
```

Lalu:

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Pastikan folder `storage` dan `bootstrap/cache` writable oleh web server.

---

## Catatan Implementasi

Aplikasi tidak memakai payment gateway karena batasan penelitian. Rekomendasi bersifat **association-rule / probabilistik**, bukan prediksi jumlah penjualan atau forecast stok. Data rating, ulasan, dan demografi pelanggan tidak digunakan. Ini mengikuti ruang lingkup proposal: rekomendasi dibentuk dari pola ko-kemunculan produk pada transaksi selesai menggunakan support, confidence, dan lift.

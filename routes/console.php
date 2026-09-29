<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('jpu:about', function () {
    $this->info('CV Jaya Pakan Utama - Sistem Pemesanan + Apriori');
})->purpose('Menampilkan identitas aplikasi.');

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending_verification' => 'Menunggu Verifikasi',
        'processing' => 'Diproses',
        'ready' => 'Siap Dikirim/Diambil',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'Menunggu Verifikasi',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
    ];

    protected $fillable = [
        'invoice_number',
        'source',
        'status',
        'payment_status',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_note',
        'subtotal',
        'grand_total',
        'payment_method',
        'payment_proof_path',
        'payment_submitted_at',
        'stock_released_at',
        'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'grand_total' => 'integer',
            'payment_submitted_at' => 'datetime',
            'stock_released_at' => 'datetime',
            'ordered_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? ucfirst($this->payment_status);
    }
}

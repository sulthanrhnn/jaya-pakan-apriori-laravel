<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AprioriItemset extends Model
{
    protected $fillable = [
        'apriori_run_id',
        'itemset_size',
        'item_codes',
        'transaction_count',
        'support',
    ];

    protected function casts(): array
    {
        return [
            'itemset_size' => 'integer',
            'item_codes' => 'array',
            'transaction_count' => 'integer',
            'support' => 'float',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(AprioriRun::class, 'apriori_run_id');
    }
}

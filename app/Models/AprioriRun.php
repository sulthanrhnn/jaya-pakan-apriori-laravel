<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AprioriRun extends Model
{
    protected $fillable = [
        'transaction_count',
        'minimum_support',
        'minimum_confidence',
        'minimum_lift',
        'max_itemset_size',
        'frequent_1_count',
        'frequent_2_count',
        'frequent_3_count',
        'eligible_rule_count',
        'duration_ms',
        'status',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'transaction_count' => 'integer',
            'minimum_support' => 'float',
            'minimum_confidence' => 'float',
            'minimum_lift' => 'float',
            'max_itemset_size' => 'integer',
            'frequent_1_count' => 'integer',
            'frequent_2_count' => 'integer',
            'frequent_3_count' => 'integer',
            'eligible_rule_count' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public function itemsets(): HasMany
    {
        return $this->hasMany(AprioriItemset::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(AprioriRule::class);
    }
}

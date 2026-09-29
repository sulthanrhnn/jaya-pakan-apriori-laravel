<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AprioriRule extends Model
{
    protected $fillable = [
        'apriori_run_id',
        'antecedent_codes',
        'consequent_codes',
        'count_antecedent',
        'count_consequent',
        'count_union',
        'support',
        'confidence',
        'support_consequent',
        'lift',
    ];

    protected function casts(): array
    {
        return [
            'antecedent_codes' => 'array',
            'consequent_codes' => 'array',
            'count_antecedent' => 'integer',
            'count_consequent' => 'integer',
            'count_union' => 'integer',
            'support' => 'float',
            'confidence' => 'float',
            'support_consequent' => 'float',
            'lift' => 'float',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(AprioriRun::class, 'apriori_run_id');
    }
}

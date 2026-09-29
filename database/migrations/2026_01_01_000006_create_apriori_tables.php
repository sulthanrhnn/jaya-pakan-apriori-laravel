<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('apriori_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('transaction_count')->default(0);
            $table->decimal('minimum_support', 10, 8);
            $table->decimal('minimum_confidence', 10, 8);
            $table->decimal('minimum_lift', 10, 8);
            $table->unsignedTinyInteger('max_itemset_size')->default(3);
            $table->unsignedInteger('frequent_1_count')->default(0);
            $table->unsignedInteger('frequent_2_count')->default(0);
            $table->unsignedInteger('frequent_3_count')->default(0);
            $table->unsignedInteger('eligible_rule_count')->default(0);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->string('status', 20)->default('running')->index();
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('apriori_itemsets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apriori_run_id')->constrained('apriori_runs')->cascadeOnDelete();
            $table->unsignedTinyInteger('itemset_size')->index();
            $table->json('item_codes');
            $table->unsignedInteger('transaction_count');
            $table->decimal('support', 12, 10);
            $table->timestamps();
        });

        Schema::create('apriori_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apriori_run_id')->constrained('apriori_runs')->cascadeOnDelete();
            $table->json('antecedent_codes');
            $table->json('consequent_codes');
            $table->unsignedInteger('count_antecedent');
            $table->unsignedInteger('count_consequent');
            $table->unsignedInteger('count_union');
            $table->decimal('support', 12, 10);
            $table->decimal('confidence', 12, 10);
            $table->decimal('support_consequent', 12, 10);
            $table->decimal('lift', 14, 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apriori_rules');
        Schema::dropIfExists('apriori_itemsets');
        Schema::dropIfExists('apriori_runs');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('description', 500);
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'occurred_at']);
            $table->index(['user_id', 'category_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

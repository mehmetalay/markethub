<?php

use App\Domain\Marketplace\Enums\MarketplaceAccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('status')->default(MarketplaceAccountStatus::Draft->value)->index();
            $table->longText('credentials');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'marketplace_id']);
            $table->unique(['tenant_id', 'marketplace_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_accounts');
    }
};

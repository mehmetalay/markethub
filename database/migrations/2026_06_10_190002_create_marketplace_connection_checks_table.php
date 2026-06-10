<?php

use App\Domain\Marketplace\Enums\MarketplaceConnectionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_connection_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_account_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(MarketplaceConnectionStatus::Pending->value)->index();
            $table->timestamp('checked_at')->nullable();
            $table->string('message')->nullable();
            $table->longText('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'marketplace_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_connection_checks');
    }
};

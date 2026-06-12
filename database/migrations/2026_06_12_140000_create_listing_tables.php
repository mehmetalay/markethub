<?php

use App\Domain\Listing\Enums\ListingPayloadType;
use App\Domain\Listing\Enums\ListingStatus;
use App\Domain\Listing\Enums\ListingVariantStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('external_id')->nullable();
            $table->string('status')->default(ListingStatus::Draft->value)->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'marketplace_account_id']);
            $table->index(['tenant_id', 'product_id']);
            $table->unique(['tenant_id', 'product_id', 'marketplace_account_id']);
        });

        Schema::create('listing_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->string('external_id')->nullable();
            $table->string('status')->default(ListingVariantStatus::Draft->value)->index();
            $table->timestamps();

            $table->index(['tenant_id', 'listing_id']);
            $table->unique(['tenant_id', 'listing_id', 'product_variant_id']);
        });

        Schema::create('listing_payloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('payload_type')->default(ListingPayloadType::Preview->value)->index();
            $table->json('payload');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'listing_id']);
        });

        Schema::create('listing_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status')->index();
            $table->string('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'listing_id']);
        });

        Schema::create('listing_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->text('message');
            $table->string('field')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'listing_id']);
            $table->index(['tenant_id', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_errors');
        Schema::dropIfExists('listing_status_histories');
        Schema::dropIfExists('listing_payloads');
        Schema::dropIfExists('listing_variants');
        Schema::dropIfExists('listings');
    }
};

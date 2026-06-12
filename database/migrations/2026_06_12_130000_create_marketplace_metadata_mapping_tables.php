<?php

use App\Domain\Marketplace\Enums\MetadataMappingStatus;
use App\Domain\Marketplace\Enums\MetadataSyncStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('parent_external_id')->nullable();
            $table->string('name');
            $table->string('path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('raw_metadata')->nullable();
            $table->timestamps();

            $table->index(['marketplace_id', 'parent_external_id']);
            $table->unique(['marketplace_id', 'external_id']);
        });

        Schema::create('marketplace_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->json('raw_metadata')->nullable();
            $table->timestamps();

            $table->unique(['marketplace_id', 'external_id']);
        });

        Schema::create('marketplace_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->json('raw_metadata')->nullable();
            $table->timestamps();

            $table->index(['marketplace_id', 'marketplace_category_id']);
            $table->unique(['marketplace_id', 'external_id']);
        });

        Schema::create('marketplace_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('value');
            $table->string('code')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('raw_metadata')->nullable();
            $table->timestamps();

            $table->index(['marketplace_id', 'marketplace_attribute_id']);
            $table->unique(['marketplace_id', 'marketplace_attribute_id', 'external_id']);
        });

        Schema::create('category_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_category_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(MetadataMappingStatus::Mapped->value)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'marketplace_id']);
            $table->unique(['tenant_id', 'marketplace_category_id']);
        });

        Schema::create('brand_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_brand_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(MetadataMappingStatus::Mapped->value)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'marketplace_id']);
            $table->unique(['tenant_id', 'marketplace_brand_id']);
        });

        Schema::create('attribute_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(MetadataMappingStatus::Mapped->value)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'marketplace_id']);
            $table->unique(['tenant_id', 'marketplace_attribute_id']);
        });

        Schema::create('attribute_value_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_mapping_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_attribute_value_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(MetadataMappingStatus::Mapped->value)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'marketplace_id']);
            $table->unique(['tenant_id', 'marketplace_attribute_value_id']);
        });

        Schema::create('metadata_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity_type')->index();
            $table->string('status')->default(MetadataSyncStatus::Pending->value)->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('message')->nullable();
            $table->json('summary')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'marketplace_id']);
        });

        Schema::create('metadata_sync_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metadata_sync_run_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type')->index();
            $table->string('external_id')->nullable();
            $table->string('status')->default(MetadataSyncStatus::Pending->value)->index();
            $table->string('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'metadata_sync_run_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metadata_sync_run_items');
        Schema::dropIfExists('metadata_sync_runs');
        Schema::dropIfExists('attribute_value_mappings');
        Schema::dropIfExists('attribute_mappings');
        Schema::dropIfExists('brand_mappings');
        Schema::dropIfExists('category_mappings');
        Schema::dropIfExists('marketplace_attribute_values');
        Schema::dropIfExists('marketplace_attributes');
        Schema::dropIfExists('marketplace_brands');
        Schema::dropIfExists('marketplace_categories');
    }
};

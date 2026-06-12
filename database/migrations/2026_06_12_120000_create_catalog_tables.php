<?php

use App\Domain\Catalog\Enums\AttributeType;
use App\Domain\Catalog\Enums\CatalogStatus;
use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Enums\ProductVariantStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('status')->default(CatalogStatus::Active->value)->index();
            $table->timestamps();

            $table->index(['tenant_id', 'parent_id']);
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('status')->default(CatalogStatus::Active->value)->index();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('type')->default(AttributeType::Text->value);
            $table->string('status')->default(CatalogStatus::Active->value)->index();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status')->default(ProductStatus::Draft->value)->index();
            $table->timestamps();

            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'brand_id']);
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('code')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->index(['tenant_id', 'attribute_id']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->string('barcode')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->default(ProductVariantStatus::Draft->value)->index();
            $table->timestamps();

            $table->index(['tenant_id', 'product_id']);
            $table->unique(['tenant_id', 'sku']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('url', 1000);
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->index(['tenant_id', 'product_id']);
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->char('currency', 3);
            $table->decimal('sale_price', 12, 2);
            $table->decimal('list_price', 12, 2)->nullable();

            $table->index(['tenant_id', 'product_variant_id']);
            $table->unique(['tenant_id', 'product_variant_id', 'currency']);
        });

        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('reserved_quantity')->default(0);

            $table->index(['tenant_id', 'product_variant_id']);
            $table->unique(['tenant_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('products');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};

<?php

use App\Domain\Marketplace\Enums\MarketplaceCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplaces', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        foreach (MarketplaceCode::cases() as $marketplaceCode) {
            DB::table('marketplaces')->insert([
                'code' => $marketplaceCode->value,
                'name' => match ($marketplaceCode) {
                    MarketplaceCode::Trendyol => 'Trendyol',
                    MarketplaceCode::TrendyolGo => 'Trendyol Go',
                    MarketplaceCode::Hepsiburada => 'Hepsiburada',
                    MarketplaceCode::N11 => 'n11',
                    MarketplaceCode::Amazon => 'Amazon',
                },
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplaces');
    }
};

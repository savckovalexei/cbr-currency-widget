<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('char_code', 10)->unique(); // USD, EUR
            $table->string('name');
            $table->integer('nominal')->default(1);
            $table->boolean('is_fetch_enabled')->default(true); // получать ли с ЦБ
            $table->boolean('is_widget_visible')->default(true); // показывать в виджете
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};

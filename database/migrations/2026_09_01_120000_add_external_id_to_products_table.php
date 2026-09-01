<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->uuid('external_id')->nullable()->unique()->after('uuid');
        });

        // Business Cloud не отдаёт ни категорию, ни привязку к терминалу,
        // поэтому импортированный товар какое-то время живёт без них.
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->unsignedBigInteger('fridge_id')->nullable()->change();
            $table->string('code')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->dropColumn('external_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->unsignedBigInteger('fridge_id')->nullable(false)->change();
            $table->string('code')->nullable(false)->change();
        });
    }
};

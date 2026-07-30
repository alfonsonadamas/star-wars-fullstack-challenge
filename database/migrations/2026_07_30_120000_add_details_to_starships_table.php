<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('starships', function (Blueprint $table): void {
            $table->unsignedBigInteger('swapi_id')->nullable()->after('id');
            $table->string('name', 120)->after('swapi_id');
            $table->unsignedBigInteger('max_atmosphering_speed')->after('name');
            $table->unsignedBigInteger('cargo_capacity')->after('max_atmosphering_speed');
        });
    }

    public function down(): void
    {
        Schema::table('starships', function (Blueprint $table): void {
            $table->dropColumn([
                'swapi_id',
                'name',
                'max_atmosphering_speed',
                'cargo_capacity',
            ]);
        });
    }
};

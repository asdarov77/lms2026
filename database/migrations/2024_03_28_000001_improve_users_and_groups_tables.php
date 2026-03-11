<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImproveUsersAndGroupsTables extends Migration
{
    public function up()
    {
        // Users table improvements
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 50)->nullable()->after('fio');
            $table->string('last_name', 50)->nullable()->after('first_name');
            $table->string('patronymic', 50)->nullable()->after('last_name');
            $table->boolean('is_active')->default(true)->after('patronymic');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->json('settings')->nullable()->after('last_login_at');
        });

        // Groups table improvements
        Schema::table('groups', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('groupdescription');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('settings')->nullable()->after('created_by');
            $table->integer('max_users')->nullable()->after('settings');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'patronymic', 'is_active', 'last_login_at', 'settings']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'created_by', 'settings', 'max_users']);
        });
    }
}

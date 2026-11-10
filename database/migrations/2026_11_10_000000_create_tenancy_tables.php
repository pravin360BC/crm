<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 80)->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('tenant_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id']);
            $table->unique(['tenant_id', 'id']);
            $table->index(['user_id', 'active']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained();
            $table->string('key', 50);
            $table->string('name');
            $table->unique(['tenant_id', 'key']);
            $table->unique(['tenant_id', 'id']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->string('key', 80)->primary();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('role_id');
            $table->string('permission_key', 80);
            $table->primary(['tenant_id', 'role_id', 'permission_key']);
            $table->foreign(['tenant_id', 'role_id'])->references(['tenant_id', 'id'])->on('roles')->cascadeOnDelete();
            $table->foreign('permission_key')->references('key')->on('permissions');
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('membership_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['tenant_id', 'membership_id', 'role_id']);
            $table->index(['tenant_id', 'role_id']);
            $table->foreign(['tenant_id', 'membership_id'])->references(['tenant_id', 'id'])->on('tenant_memberships')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'role_id'])->references(['tenant_id', 'id'])->on('roles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['user_roles', 'role_permissions', 'permissions', 'roles', 'tenant_memberships', 'tenants'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

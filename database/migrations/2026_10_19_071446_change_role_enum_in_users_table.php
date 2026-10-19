<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL tidak support ALTER COLUMN enum langsung,
        // jadi ubah ke string dulu, update data, lalu tambah constraint
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255)");
        DB::statement("UPDATE users SET role = 'pemilik' WHERE role = 'admin'");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'pemilik', 'penyewa'))");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'penyewa'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("UPDATE users SET role = 'admin' WHERE role = 'pemilik'");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'penyewa'))");
    }
};

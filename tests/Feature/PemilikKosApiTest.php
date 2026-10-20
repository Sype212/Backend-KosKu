<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PemilikKosApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('penyewa');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('kos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->text('alamat');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_per_bulan', 14, 2);
            $table->unsignedInteger('jumlah_kamar');
            $table->unsignedInteger('kamar_tersedia');
            $table->timestamps();
        });
    }

    public function test_owner_can_manage_kos_and_availability(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'password',
            'role' => 'pemilik',
        ]);
        Sanctum::actingAs($owner);

        $created = $this->postJson('/api/pemilik/kos', [
            'nama' => 'Kos Melati',
            'alamat' => 'Jl. Mawar 1',
            'harga_per_bulan' => 1200000,
            'jumlah_kamar' => 4,
            'kamar_tersedia' => 2,
        ])->assertCreated()
            ->assertJsonPath('data.nama', 'Kos Melati')
            ->assertJsonPath('data.status', 'tersedia');

        $id = $created->json('data.id');

        $this->getJson('/api/pemilik/kos')
            ->assertOk()
            ->assertJsonCount(1, 'data.data');

        $this->getJson('/api/pemilik/kos/'.$id)
            ->assertOk()
            ->assertJsonPath('data.id', $id);

        $this->putJson('/api/pemilik/kos/'.$id, ['jumlah_kamar' => 1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('kamar_tersedia');

        $this->patchJson('/api/pemilik/kos/'.$id.'/ketersediaan', ['kamar_tersedia' => 0])
            ->assertOk()
            ->assertJsonPath('data.status', 'penuh');

        $this->putJson('/api/pemilik/kos/'.$id, ['nama' => 'Kos Melati Baru'])
            ->assertOk()
            ->assertJsonPath('data.nama', 'Kos Melati Baru');

        $this->getJson('/api/pemilik/dashboard')
            ->assertOk()
            ->assertJsonPath('data.total_kos', 1)
            ->assertJsonPath('data.total_kamar', 4)
            ->assertJsonPath('data.kamar_tersedia', 0)
            ->assertJsonPath('data.kos_tersedia', 0);

        $this->deleteJson('/api/pemilik/kos/'.$id)
            ->assertOk();

        $this->assertDatabaseMissing('kos', ['id' => $id]);
    }

    public function test_owner_cannot_access_another_owners_kos(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'password',
            'role' => 'pemilik',
        ]);
        $otherOwner = User::create([
            'name' => 'Other Owner',
            'email' => 'other@example.test',
            'password' => 'password',
            'role' => 'pemilik',
        ]);
        $kos = $otherOwner->kos()->create([
            'nama' => 'Kos Rahasia',
            'alamat' => 'Jl. Kenanga 2',
            'harga_per_bulan' => 900000,
            'jumlah_kamar' => 2,
            'kamar_tersedia' => 1,
        ]);
        Sanctum::actingAs($owner);

        $this->getJson('/api/pemilik/kos/'.$kos->id)->assertNotFound();
        $this->putJson('/api/pemilik/kos/'.$kos->id, ['nama' => 'Diubah'])->assertNotFound();
        $this->patchJson('/api/pemilik/kos/'.$kos->id.'/ketersediaan', ['kamar_tersedia' => 0])->assertNotFound();
        $this->deleteJson('/api/pemilik/kos/'.$kos->id)->assertNotFound();

        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'nama' => 'Kos Rahasia', 'kamar_tersedia' => 1]);
    }

    public function test_only_authenticated_owners_can_access_owner_routes(): void
    {
        $this->getJson('/api/pemilik/kos')->assertUnauthorized();

        $tenant = User::create([
            'name' => 'Tenant',
            'email' => 'tenant@example.test',
            'password' => 'password',
            'role' => 'penyewa',
        ]);
        Sanctum::actingAs($tenant);

        $this->getJson('/api/pemilik/kos')->assertForbidden();
    }
}

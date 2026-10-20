<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PemilikKosController extends Controller
{
    use ApiResponse;

    public function dashboard(Request $request): JsonResponse
    {
        return $this->success([
            'total_kos' => $request->user()->kos()->count(),
            'total_kamar' => $request->user()->kos()->sum('jumlah_kamar'),
            'kamar_tersedia' => $request->user()->kos()->sum('kamar_tersedia'),
            'kos_tersedia' => $request->user()->kos()->where('kamar_tersedia', '>', 0)->count(),
        ], 'Dashboard pemilik berhasil diambil');
    }

    public function index(Request $request): JsonResponse
    {
        $kos = $request->user()->kos()->latest()->paginate(15);

        return $this->success($kos, 'Daftar kos berhasil diambil');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $kos = $request->user()->kos()->findOrFail($id);

        return $this->success($kos, 'Detail kos berhasil diambil');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string'],
            'deskripsi' => ['nullable', 'string'],
            'harga_per_bulan' => ['required', 'numeric', 'min:0'],
            'jumlah_kamar' => ['required', 'integer', 'min:1'],
            'kamar_tersedia' => ['required', 'integer', 'min:0', 'lte:jumlah_kamar'],
        ]);

        $kos = $request->user()->kos()->create($data);

        return $this->success($kos, 'Kos berhasil ditambahkan', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $kos = $request->user()->kos()->findOrFail($id);
        $data = $request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:150'],
            'alamat' => ['sometimes', 'required', 'string'],
            'deskripsi' => ['sometimes', 'nullable', 'string'],
            'harga_per_bulan' => ['sometimes', 'required', 'numeric', 'min:0'],
            'jumlah_kamar' => ['sometimes', 'required', 'integer', 'min:1'],
            'kamar_tersedia' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $jumlahKamar = $data['jumlah_kamar'] ?? $kos->jumlah_kamar;
        $kamarTersedia = $data['kamar_tersedia'] ?? $kos->kamar_tersedia;

        if ($kamarTersedia > $jumlahKamar) {
            return $this->error(
                'Jumlah kamar tersedia tidak boleh melebihi jumlah kamar.',
                422,
                ['kamar_tersedia' => ['Jumlah kamar tersedia tidak boleh melebihi jumlah kamar.']],
            );
        }

        $kos->update($data);

        return $this->success($kos->fresh(), 'Data kos berhasil diperbarui');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $kos = $request->user()->kos()->findOrFail($id);
        $kos->delete();

        return $this->success(null, 'Kos berhasil dihapus');
    }

    public function updateKetersediaan(Request $request, int $id): JsonResponse
    {
        $kos = $request->user()->kos()->findOrFail($id);
        $data = $request->validate([
            'kamar_tersedia' => ['required', 'integer', 'min:0', 'max:'.$kos->jumlah_kamar],
        ]);

        $kos->update($data);

        return $this->success($kos->fresh(), 'Ketersediaan kamar berhasil diperbarui');
    }
}

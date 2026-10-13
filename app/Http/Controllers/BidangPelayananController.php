<?php

namespace App\Http\Controllers;

use App\Models\BidangPelayanan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BidangPelayananController extends Controller
{
    public function index()
    {
        return Inertia::render('bidang-pelayanan', [
            'bidangPelayanan' => BidangPelayanan::orderBy('order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBidang($request);

        BidangPelayanan::create([
            ...$validated,
            'order' => (BidangPelayanan::max('order') ?? 0) + 1,
        ]);

        return redirect()->route('bidang-pelayanan')->with('success', 'Bidang pelayanan berhasil ditambahkan.');
    }

    public function update(Request $request, BidangPelayanan $bidangPelayanan)
    {
        $bidangPelayanan->update($this->validateBidang($request));

        return redirect()->route('bidang-pelayanan')->with('success', 'Bidang pelayanan berhasil diperbarui.');
    }

    public function destroy(BidangPelayanan $bidangPelayanan)
    {
        $bidangPelayanan->delete();

        return redirect()->route('bidang-pelayanan')->with('success', 'Bidang pelayanan berhasil dihapus.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            BidangPelayanan::where('id', $id)->update(['order' => $index + 1]);
        }

        return redirect()->route('bidang-pelayanan')->with('success', 'Urutan bidang pelayanan berhasil diperbarui.');
    }

    /**
     * @return array{name: string, pic_name: string, pic_phone: string}
     */
    private function validateBidang(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'pic_name' => 'required|string|max:255',
            // International format without the leading 0, so the public site can build wa.me links.
            'pic_phone' => ['required', 'string', 'regex:/^628\d{7,11}$/'],
        ], [
            'pic_phone.regex' => 'Nomor telepon harus diawali 628 dan hanya berisi angka (contoh: 6281234567890).',
        ], [
            'name' => 'nama bidang pelayanan',
            'pic_name' => 'nama PIC',
            'pic_phone' => 'nomor telepon PIC',
        ]);
    }
}

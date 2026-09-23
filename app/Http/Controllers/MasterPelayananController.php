<?php

namespace App\Http\Controllers;

use App\Models\Pelayanan;
use App\Support\CloudinaryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MasterPelayananController extends Controller
{
    /**
     * Master list of pelayanan (ministries). Add/rename/delete/reorder here;
     * the Pelayanan page only edits each row's content (images, description,
     * detail cards).
     */
    public function index()
    {
        return Inertia::render('master/pelayanan', [
            'pelayanan' => Pelayanan::withCount(['images', 'details'])
                ->orderBy('order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Pelayanan::create([
            'slug' => $this->uniqueSlug($validated['name']),
            'title' => $validated['name'],
            'subtitle' => '',
            'description' => '',
            'order' => (Pelayanan::max('order') ?? 0) + 1,
        ]);

        return redirect()->route('master.pelayanan')->with('success', 'Pelayanan berhasil ditambahkan.');
    }

    /**
     * Renames the ministry (Pelayanan.title). The slug is set once on create
     * and never changes here, so links such as /pelayanan?tab=<slug> on the
     * public site keep working after a rename.
     */
    public function update(Request $request, Pelayanan $pelayanan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $pelayanan->update(['title' => $validated['name']]);

        return redirect()->route('master.pelayanan')->with('success', 'Pelayanan berhasil diperbarui.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Pelayanan::where('id', $id)->update(['order' => $index + 1]);
        }

        return redirect()->route('master.pelayanan')->with('success', 'Urutan pelayanan berhasil diperbarui.');
    }

    public function destroy(Pelayanan $pelayanan)
    {
        foreach ($pelayanan->images as $image) {
            CloudinaryImage::delete($image->public_id);
        }

        // Cascades to pelayanan_details and pelayanan_images at the DB level.
        $pelayanan->delete();

        return redirect()->route('master.pelayanan')->with('success', 'Pelayanan berhasil dihapus.');
    }

    /**
     * "poliklinik", "poliklinik-2", "poliklinik-3", ... — generated once on
     * create and immutable afterwards.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'pelayanan';
        $slug = $base;
        $suffix = 2;

        while (Pelayanan::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}

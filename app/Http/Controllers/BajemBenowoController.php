<?php

namespace App\Http\Controllers;

use App\Models\BajemBenowoItem;
use App\Models\BajemBenowoSetting;
use App\Support\CloudinaryImage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BajemBenowoController extends Controller
{
    private const SLOTS = ['about', 'location'];

    public function index()
    {
        $settings = BajemBenowoSetting::current();

        return Inertia::render('bajem-benowo', [
            'settings' => $settings,
            'items' => [
                'ibadah' => BajemBenowoItem::where('section', BajemBenowoItem::SECTION_IBADAH)
                    ->orderBy('order')->orderBy('id')->get(),
                'pelayanan' => BajemBenowoItem::where('section', BajemBenowoItem::SECTION_PELAYANAN)
                    ->orderBy('order')->orderBy('id')->get(),
            ],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'about_description' => 'nullable|string',
            'pelayanan_intro' => 'nullable|string',
            'address' => 'nullable|string',
            'maps_url' => 'nullable|url|max:255',
            'map_embed_url' => 'nullable|string',
        ]);

        BajemBenowoSetting::current()->update($validated);

        return redirect()->route('bajem-benowo')->with('success', 'Pengaturan Bajem Benowo berhasil diperbarui.');
    }

    public function updateSettingImage(Request $request, string $slot)
    {
        abort_unless(in_array($slot, self::SLOTS, true), 404);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,avif|max:20480',
        ]);

        $settings = BajemBenowoSetting::current();
        $publicIdField = "{$slot}_image_public_id";
        $urlField = "{$slot}_image_url";

        CloudinaryImage::delete($settings->{$publicIdField});

        $uploaded = CloudinaryImage::upload($request->file('image')->getRealPath(), 'bajem-benowo/'.$slot);

        $settings->update([
            $publicIdField => $uploaded['public_id'],
            $urlField => $uploaded['url'],
        ]);

        return redirect()->route('bajem-benowo')->with('success', 'Gambar berhasil diperbarui.');
    }

    public function destroySettingImage(string $slot)
    {
        abort_unless(in_array($slot, self::SLOTS, true), 404);

        $settings = BajemBenowoSetting::current();
        $publicIdField = "{$slot}_image_public_id";
        $urlField = "{$slot}_image_url";

        CloudinaryImage::delete($settings->{$publicIdField});

        $settings->update([
            $publicIdField => null,
            $urlField => null,
        ]);

        return redirect()->route('bajem-benowo')->with('success', 'Gambar berhasil dihapus.');
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'section' => ['required', Rule::in(BajemBenowoItem::SECTIONS)],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schedules' => 'nullable|array',
            'schedules.*' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'audience' => 'nullable|string|max:255',
            'cadence' => 'nullable|string|max:255',
        ]);

        BajemBenowoItem::create([
            ...$validated,
            'order' => (BajemBenowoItem::where('section', $validated['section'])->max('order') ?? 0) + 1,
        ]);

        return redirect()->route('bajem-benowo')->with('success', 'Item berhasil ditambahkan.');
    }

    public function updateItem(Request $request, BajemBenowoItem $item)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schedules' => 'nullable|array',
            'schedules.*' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'audience' => 'nullable|string|max:255',
            'cadence' => 'nullable|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('bajem-benowo')->with('success', 'Item berhasil diperbarui.');
    }

    public function updateItemImage(Request $request, BajemBenowoItem $item)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,avif|max:20480',
        ]);

        CloudinaryImage::delete($item->image_public_id);

        $uploaded = CloudinaryImage::upload($request->file('image')->getRealPath(), 'bajem-benowo/'.$item->section);

        $item->update([
            'image_public_id' => $uploaded['public_id'],
            'image_url' => $uploaded['url'],
        ]);

        return redirect()->route('bajem-benowo')->with('success', 'Gambar berhasil diperbarui.');
    }

    public function destroyItemImage(BajemBenowoItem $item)
    {
        CloudinaryImage::delete($item->image_public_id);

        $item->update([
            'image_public_id' => null,
            'image_url' => null,
        ]);

        return redirect()->route('bajem-benowo')->with('success', 'Gambar berhasil dihapus.');
    }

    public function destroyItem(BajemBenowoItem $item)
    {
        CloudinaryImage::delete($item->image_public_id);
        $item->delete();

        return redirect()->route('bajem-benowo')->with('success', 'Item berhasil dihapus.');
    }

    public function reorderItems(Request $request)
    {
        $validated = $request->validate([
            'section' => ['required', Rule::in(BajemBenowoItem::SECTIONS)],
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            BajemBenowoItem::where('id', $id)
                ->where('section', $validated['section'])
                ->update(['order' => $index + 1]);
        }

        return redirect()->route('bajem-benowo')->with('success', 'Urutan berhasil diperbarui.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::latest()->paginate(20);

        return view('assets.index', compact('assets'));
    }

    public function create()
    {
        return view('assets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'nullable|string|max:255',
            'quantity'       => 'required|integer|min:1',
            'purchase_date'  => 'nullable|date',
            'purchase_cost'  => 'nullable|numeric|min:0',
            'current_value'  => 'nullable|numeric|min:0',
            'condition'      => 'required|string',
            'status'         => 'required|string',
            'description'    => 'nullable|string',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('assets', 'public');
        }

        Asset::create($data);

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset added successfully.');
    }

    public function show(Asset $asset)
    {
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $assets = Asset::latest()->paginate(20);

        return view('assets.index', [
            'assets' => $assets,
            'editAsset' => $asset
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'nullable|string|max:255',
            'quantity'       => 'required|integer|min:1',
            'purchase_date'  => 'nullable|date',
            'purchase_cost'  => 'nullable|numeric|min:0',
            'current_value'  => 'nullable|numeric|min:0',
            'condition'      => 'required|string',
            'status'         => 'required|string',
            'description'    => 'nullable|string',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {

            if ($asset->image) {
                Storage::disk('public')->delete($asset->image);
            }

            $data['image'] = $request->file('image')
                ->store('assets', 'public');
        }

        $asset->update($data);

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->image) {
            Storage::disk('public')->delete($asset->image);
        }

        $asset->delete();

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset deleted successfully.');
    }
}
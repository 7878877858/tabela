<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value','key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'farm_name'     => 'required|string|max:100',
            'primary_color' => 'required|string',
            'milk_price'    => 'required|numeric|min:0',
            'currency'      => 'required|string|max:5',
        ]);

        foreach ($request->except('_token','_method') as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'સેટિંગ્સ સેવ થઈ! પેઇજ રિફ્રેશ કરો.');
    }
}
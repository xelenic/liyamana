<?php

namespace Modules\NanoBananaModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\NanoBananaModule\Models\NanoBananaTemplate;

class NanoBananaTemplateController extends Controller
{
    public function index()
    {
        $templates = NanoBananaTemplate::ordered()->get();
        return view('nano-banana-module::admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('nano-banana-module::admin.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prompt' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'defined_fields' => 'nullable|json',
            'upload_image' => 'required|boolean',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['defined_fields'] = $validated['defined_fields'] ? json_decode($validated['defined_fields'], true) : [];
        $validated['upload_image'] = (bool) $validated['upload_image'];
        $validated['is_active'] = (bool) $validated['is_active'];
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('nano-banana-templates', 'public');
        } else {
            $validated['image_path'] = null;
        }

        unset($validated['image']);
        NanoBananaTemplate::create($validated);

        return redirect()->route('admin.nanobanana.templates.index')->with('success', 'Template created successfully.');
    }

    public function edit(NanoBananaTemplate $template)
    {
        return view('nano-banana-module::admin.templates.edit', compact('template'));
    }

    public function update(Request $request, NanoBananaTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prompt' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'defined_fields' => 'nullable|json',
            'upload_image' => 'required|boolean',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['defined_fields'] = $validated['defined_fields'] ? json_decode($validated['defined_fields'], true) : [];
        $validated['upload_image'] = (bool) $validated['upload_image'];
        $validated['is_active'] = (bool) $validated['is_active'];
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('image')) {
            if ($template->image_path) {
                Storage::disk('public')->delete($template->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('nano-banana-templates', 'public');
        }

        unset($validated['image']);
        $template->update($validated);

        return redirect()->route('admin.nanobanana.templates.index')->with('success', 'Template updated successfully.');
    }

    public function destroy(NanoBananaTemplate $template)
    {
        if ($template->image_path) {
            Storage::disk('public')->delete($template->image_path);
        }
        $template->delete();
        return redirect()->route('admin.nanobanana.templates.index')->with('success', 'Template deleted.');
    }

    public function settings()
    {
        $settings = [
            'gemini_api_key' => Setting::get('gemini_api_key', ''),
            'gemini_image_model' => Setting::get('gemini_image_model', 'gemini-3-pro-image-preview'),
            'gemini_image_cost' => Setting::get('gemini_image_cost', 1),
            'nanobanana_image_size' => Setting::get('nanobanana_image_size', '1:1'),
            'nanobanana_resolution' => Setting::get('nanobanana_resolution', '2K'),
        ];
        return view('nano-banana-module::admin.settings', $settings);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'gemini_api_key' => 'nullable|string|max:500',
            'gemini_image_model' => 'nullable|string|in:gemini-2.5-flash-image,gemini-3-pro-image-preview',
            'gemini_image_cost' => 'nullable|numeric|min:0',
            'nanobanana_image_size' => 'nullable|string|in:1:1,16:9,9:16,4:3,3:4,2:3,3:2,4:5,5:4,21:9,auto',
            'nanobanana_resolution' => 'nullable|string|in:1K,2K,4K',
        ]);

        Setting::set('gemini_api_key', $request->input('gemini_api_key', ''), 'nanobanana');
        Setting::set('gemini_image_model', $request->input('gemini_image_model', 'gemini-3-pro-image-preview'), 'nanobanana');
        Setting::set('gemini_image_cost', $request->input('gemini_image_cost', 1), 'nanobanana');
        Setting::set('nanobanana_image_size', $request->input('nanobanana_image_size', '1:1'), 'nanobanana');
        Setting::set('nanobanana_resolution', $request->input('nanobanana_resolution', '2K'), 'nanobanana');

        return redirect()->route('admin.nanobanana.settings')->with('success', 'Settings saved.');
    }
}

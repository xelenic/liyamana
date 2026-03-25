<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignFont;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DesignFontController extends Controller
{
    private const ALLOWED_EXT = ['ttf', 'otf', 'woff', 'woff2', 'eot'];

    public function index(): View
    {
        $fonts = DesignFont::query()->ordered()->paginate(30);

        return view('admin.design-fonts.index', compact('fonts'));
    }

    public function create(): View
    {
        return view('admin.design-fonts.form', ['font' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:design_fonts,name'],
            'font_file' => ['required', 'file', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        $file = $request->file('font_file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_EXT, true)) {
            return redirect()->back()->withInput()->with('error', 'Invalid file type. Allowed: '.implode(', ', self::ALLOWED_EXT));
        }

        $basePath = 'design-fonts';
        if (! Storage::disk('public')->exists($basePath)) {
            Storage::disk('public')->makeDirectory($basePath);
        }

        $filename = time().'_'.uniqid('', true).'.'.$extension;
        $storedPath = $file->storeAs($basePath, $filename, 'public');

        DesignFont::create([
            'name' => trim($data['name']),
            'stored_path' => $storedPath,
            'original_filename' => $file->getClientOriginalName(),
            'extension' => $extension,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.design-fonts.index')->with('success', 'Font uploaded and available in the design editor.');
    }

    public function edit(int $id): View
    {
        $font = DesignFont::findOrFail($id);

        return view('admin.design-fonts.form', compact('font'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $font = DesignFont::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:design_fonts,name,'.$font->id],
            'font_file' => ['nullable', 'file', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        if ($request->hasFile('font_file')) {
            $file = $request->file('font_file');
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, self::ALLOWED_EXT, true)) {
                return redirect()->back()->withInput()->with('error', 'Invalid file type. Allowed: '.implode(', ', self::ALLOWED_EXT));
            }

            if (Storage::disk('public')->exists($font->stored_path)) {
                Storage::disk('public')->delete($font->stored_path);
            }

            $basePath = 'design-fonts';
            $filename = time().'_'.uniqid('', true).'.'.$extension;
            $storedPath = $file->storeAs($basePath, $filename, 'public');

            $font->stored_path = $storedPath;
            $font->original_filename = $file->getClientOriginalName();
            $font->extension = $extension;
        }

        $font->name = trim($data['name']);
        $font->is_active = $request->boolean('is_active', true);
        $font->sort_order = (int) ($data['sort_order'] ?? $font->sort_order);
        $font->save();

        return redirect()->route('admin.design-fonts.index')->with('success', 'Font updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $font = DesignFont::findOrFail($id);

        if (Storage::disk('public')->exists($font->stored_path)) {
            Storage::disk('public')->delete($font->stored_path);
        }

        $font->delete();

        return redirect()->route('admin.design-fonts.index')->with('success', 'Font removed.');
    }
}

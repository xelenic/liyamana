<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\Module\ModuleInstaller;
use App\Services\Module\ModuleRegistry;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function __construct(
        protected ModuleRegistry $registry,
        protected ModuleInstaller $installer
    ) {}

    /**
     * List installed modules and show upload form.
     */
    public function index()
    {
        $modules = $this->registry->getAll();

        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Upload and install a module from ZIP.
     */
    public function store(Request $request)
    {
        $request->validate([
            'module_zip' => 'required|file|mimes:zip|max:10240', // 10MB
        ]);

        try {
            $module = $this->installer->install($request->file('module_zip'));
            return redirect()->route('admin.modules.index')
                ->with('success', "Module '{$module->name}' installed successfully.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.modules.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle module enabled/disabled.
     */
    public function toggle(string $name)
    {
        $module = Module::where('name', $name)->firstOrFail();
        $module->update(['enabled' => ! $module->enabled]);
        $this->registry->clearCache();

        $status = $module->enabled ? 'enabled' : 'disabled';
        return redirect()->route('admin.modules.index')
            ->with('success', "Module '{$name}' has been {$status}.");
    }

    /**
     * Uninstall a module.
     */
    public function destroy(string $name)
    {
        try {
            $this->installer->uninstall($name);
            return redirect()->route('admin.modules.index')
                ->with('success', "Module '{$name}' has been uninstalled.");
        } catch (\Exception $e) {
            return redirect()->route('admin.modules.index')
                ->with('error', 'Failed to uninstall: ' . $e->getMessage());
        }
    }
}

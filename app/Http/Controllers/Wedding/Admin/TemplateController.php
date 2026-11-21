<?php

namespace App\Http\Controllers\Wedding\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\Wedding\TemplateRegistry;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(TemplateRegistry $registry)
    {
        return view('wedding.admin.templates', [
            'templates' => Template::withCount('weddings')->orderBy('sort_order')->orderBy('id')->get(),
            'meta' => $registry->allMeta(),
        ]);
    }

    public function update(Request $request, Template $template)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'style_label' => ['required', 'string', 'max:80'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $template->update($data);

        return back()->with('status', 'Template '.$template->name.' diperbarui.');
    }
}

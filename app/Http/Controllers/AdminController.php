<?php
namespace App\Http\Controllers;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        return view('admin.index', ['userCount' => User::count(), 'weddingCount' => Wedding::count(), 'publishedCount' => Wedding::where('status', 'published')->count(), 'templates' => Template::orderBy('sort_order')->get(), 'weddings' => Wedding::with('user','template')->latest()->limit(8)->get()]);
    }

    public function toggleTemplate(Request $request, Template $template)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $template->update(['is_active' => ! $template->is_active]);
        return back()->with('success', 'Status template diperbarui.');
    }
}

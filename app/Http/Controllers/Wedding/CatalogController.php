<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\Wedding\DemoInvitation;
use App\Services\Wedding\TemplateRegistry;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(TemplateRegistry $registry)
    {
        return view('wedding.public.templates', [
            'templates' => $registry->active(),
        ]);
    }

    public function show(string $slug, TemplateRegistry $registry)
    {
        abort_unless($registry->isKnown($slug), 404);
        $template = $registry->active()->firstWhere('slug', $slug)
            ?? Template::where('slug', $slug)->firstOrFail();

        return view('wedding.public.template-show', [
            'template' => $template,
            'wedding' => DemoInvitation::forTemplate($slug),
            'meta' => $registry->meta($slug),
        ]);
    }

    public function preview(string $slug, TemplateRegistry $registry)
    {
        abort_unless($registry->isKnown($slug), 404);
        $wedding = DemoInvitation::forTemplate($slug);

        return response()
            ->view('wedding.templates.'.$slug.'.index', [
                'wedding' => $wedding,
                'recipientName' => 'Bapak/Ibu/Saudara/i',
                'guest' => null,
                'myRsvp' => null,
                'isPreview' => true,
                'previewMode' => true,
                'embed' => true,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function use(Request $request, string $slug, TemplateRegistry $registry)
    {
        abort_unless($registry->isKnown($slug), 404);

        if (! $request->user()) {
            $target = route('wedding.templates.use', $slug);

            return redirect()->guest(route('wedding.login'))->with('url.intended', $target);
        }

        if ($request->user()->isAdmin()) {
            return redirect()->route('wedding.admin.templates.index');
        }

        return redirect()->route('wedding.invitations.create', ['template' => $slug]);
    }
}

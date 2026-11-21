<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Services\Wedding\TemplateRegistry;

class LandingController extends Controller
{
    public function __invoke(TemplateRegistry $registry)
    {
        return view('wedding.public.landing', [
            'templates' => $registry->active(),
            'stats' => [
                'template' => count(config('wedding.templates')),
                'acara' => 'Tak terbatas',
                'rsvp' => 'Real-time',
            ],
        ]);
    }
}

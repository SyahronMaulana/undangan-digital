<?php

namespace App\Http\Controllers\Wedding\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)
            ->withCount([
                'weddings',
                'weddings as published_weddings_count' => fn ($q) => $q->where('status', 'published'),
            ])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn ($inner) => $inner->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term));
            })
            ->latest()->paginate(20)->withQueryString();

        return view('wedding.admin.customers', compact('customers'));
    }

    public function show(User $user)
    {
        abort_unless($user->isCustomer(), 404);

        return view('wedding.admin.customer-show', [
            'customer' => $user->load(['weddings.template', 'weddings.events']),
        ]);
    }
}

@extends('layouts.dashboard')
@section('title', 'Undangan Saya')
@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Undangan Saya</h1>
    <a href="{{ route('dashboard.invitations.create') }}" class="rounded-md bg-emerald-900 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">Buat undangan</a>
</div>
<div class="mt-6 space-y-3">
    @forelse ($weddings as $w)
        <a href="{{ route('dashboard.invitations.show', $w) }}" class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-stone-200 bg-white p-4 hover:border-emerald-700">
            <div>
                <p class="font-medium">{{ $w->coupleNames() }}</p>
                <p class="text-sm text-stone-600">/{{ $w->slug }} · Desain {{ $w->template->name }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $w->isPublished() ? 'bg-emerald-100 text-emerald-900' : 'bg-stone-100 text-stone-700' }}">{{ $w->isPublished() ? 'Terbit' : 'Draf' }}</span>
        </a>
    @empty
        <p class="text-stone-600">Belum ada undangan. Mulai dengan memilih desain dan mengisi nama kedua mempelai.</p>
    @endforelse
</div>
@endsection

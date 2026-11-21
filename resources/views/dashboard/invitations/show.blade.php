@extends('layouts.dashboard')
@section('title', $wedding->coupleNames())
@section('content')
<h1 class="text-2xl font-semibold">{{ $wedding->coupleNames() }}</h1>
<p class="mt-1 text-stone-600">Desain {{ $wedding->template->name }} · {{ $wedding->isPublished() ? 'Terbit' : 'Draf' }}</p>

<div class="mt-6 max-w-xl rounded-lg border border-stone-200 bg-white p-5">
    <p class="text-sm text-stone-600">Link undangan</p>
    <p class="mt-1 break-all font-mono text-sm">{{ route('invitation.show', $wedding->slug) }}</p>
    <div class="mt-4 flex flex-wrap gap-3">
        <a href="{{ route('invitation.show', $wedding->slug) }}" target="_blank" rel="noopener" class="rounded-md border border-stone-300 px-4 py-2 text-sm hover:bg-stone-50">Lihat undangan</a>
        <form method="POST" action="{{ route('dashboard.invitations.publish', $wedding) }}">@csrf
            <button class="rounded-md bg-emerald-900 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">{{ $wedding->isPublished() ? 'Kembalikan ke draf' : 'Publikasikan' }}</button>
        </form>
        <form method="POST" action="{{ route('dashboard.invitations.destroy', $wedding) }}" onsubmit="return confirm('Hapus undangan ini beserta seluruh datanya?')">@csrf @method('DELETE')
            <button class="rounded-md border border-red-300 px-4 py-2 text-sm text-red-800 hover:bg-red-50">Hapus</button>
        </form>
    </div>
</div>
<p class="mt-6 text-sm text-stone-500">Editor lengkap dengan pratinjau langsung dibuat pada Tahap 5.</p>
@endsection

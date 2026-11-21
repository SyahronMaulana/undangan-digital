@extends('layouts.dashboard')
@section('title', 'Dasbor')
@section('content')
<h1 class="text-2xl font-semibold">Halo, {{ auth()->user()->name }}</h1>
<p class="mt-1 text-stone-600">Paket Anda: <strong>{{ $package->name }}</strong></p>

<dl class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-3">
    @foreach ([['Total undangan',$stats['undangan']],['Total tamu',$stats['tamu']],['Akan hadir',$stats['hadir']],['Tidak dapat hadir',$stats['tidak_hadir']],['Belum konfirmasi',$stats['belum']],['Jumlah orang hadir',$stats['orang_hadir']]] as [$label,$value])
        <div class="rounded-lg border border-stone-200 bg-white p-4">
            <dt class="text-sm text-stone-600">{{ $label }}</dt>
            <dd class="mt-1 text-2xl font-semibold">{{ $value }}</dd>
        </div>
    @endforeach
</dl>

<h2 class="mt-10 text-lg font-semibold">Undangan terbaru</h2>
@forelse ($recent as $w)
    <a href="{{ route('dashboard.invitations.show', $w) }}" class="mt-3 flex items-center justify-between rounded-lg border border-stone-200 bg-white p-4 hover:border-emerald-700">
        <span>{{ $w->coupleNames() }}</span>
        <span class="text-sm text-stone-600">{{ $w->isPublished() ? 'Terbit' : 'Draf' }}</span>
    </a>
@empty
    <p class="mt-3 text-stone-600">Belum ada undangan. <a class="font-medium text-emerald-900 underline" href="{{ route('dashboard.invitations.create') }}">Buat undangan pertama Anda</a>.</p>
@endforelse
@endsection

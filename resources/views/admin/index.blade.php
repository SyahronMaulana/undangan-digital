@extends('dashboard.layout')
@section('content')
<header class="dash-head"><div><p class="eyebrow">ADMIN MARKAS PUNOKAWAN</p><h1>Dasbor Platform</h1><p>Kelola customer, undangan, dan koleksi template.</p></div></header>
<section class="dash-stat"><article><b>{{ $userCount }}</b><span>Total customer</span></article><article><b>{{ $weddingCount }}</b><span>Total undangan</span></article><article><b>{{ $publishedCount }}</b><span>Undangan online</span></article></section>
<section class="dash-list"><h2>Status Template</h2>@foreach($templates as $template)<article><div class="dash-thumb {{ $template->slug }}">{{ $template->name }}</div><div><h3>{{ $template->name }}</h3><p>{{ $template->is_active ? 'Aktif di katalog customer' : 'Dinonaktifkan' }}</p></div><form method="post" action="{{ route('wedding.admin.templates.toggle',$template) }}">@csrf @method('PATCH')<button class="pill dark">{{ $template->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></article>@endforeach</section>
<section class="dash-list"><h2>Undangan Terbaru</h2>@forelse($weddings as $wedding)<article><div><h3>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h3><p>{{ $wedding->user->name }} · {{ $wedding->template?->name ?? 'Belum memilih template' }} · {{ $wedding->status }}</p></div></article>@empty<p class="empty">Belum ada undangan.</p>@endforelse</section>
@endsection

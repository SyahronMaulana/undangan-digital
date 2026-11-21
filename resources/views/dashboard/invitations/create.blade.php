@extends('layouts.dashboard')
@section('title', 'Buat Undangan')
@section('content')
<h1 class="text-2xl font-semibold">Buat Undangan</h1>

@if ($limitReached)
    <div class="mt-4 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Paket {{ $package->name }} hanya mendukung {{ $package->max_invitations }} undangan. Tingkatkan paket untuk membuat lebih banyak.
    </div>
@endif
@error('limit')<div class="mt-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>@enderror

<form method="POST" action="{{ route('dashboard.invitations.store') }}" class="mt-6 max-w-xl space-y-5">
    @csrf
    @foreach ([['groom_name','Nama mempelai pria'],['bride_name','Nama mempelai wanita']] as [$f,$label])
        <div>
            <label for="{{ $f }}" class="block text-sm font-medium">{{ $label }}</label>
            <input id="{{ $f }}" name="{{ $f }}" value="{{ old($f) }}" required class="mt-1 w-full rounded-md border border-stone-300 px-3 py-2">
            @error($f)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
    @endforeach
    <div>
        <label for="slug" class="block text-sm font-medium">Alamat undangan</label>
        <div class="mt-1 flex items-center rounded-md border border-stone-300 bg-white">
            <span class="px-3 text-sm text-stone-500">{{ rtrim(config('app.url'), '/') }}/</span>
            <input id="slug" name="slug" value="{{ old('slug') }}" required placeholder="andi-siti" class="w-full rounded-r-md border-0 px-2 py-2 focus:ring-1 focus:ring-emerald-700">
        </div>
        @error('slug')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="event_date" class="block text-sm font-medium">Tanggal pernikahan</label>
        <input id="event_date" type="date" name="event_date" value="{{ old('event_date') }}" class="mt-1 rounded-md border border-stone-300 px-3 py-2">
        @error('event_date')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <fieldset>
        <legend class="text-sm font-medium">Desain</legend>
        <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($templates as $t)
                <label class="cursor-pointer rounded-lg border border-stone-300 bg-white p-3 has-[:checked]:border-emerald-800 has-[:checked]:ring-1 has-[:checked]:ring-emerald-800">
                    <input type="radio" name="template_id" value="{{ $t->id }}" class="sr-only" required @checked(old('template_id') == $t->id)>
                    <div class="flex h-10 overflow-hidden rounded">
                        @foreach (($t->palette ?? []) as $c)<span class="flex-1" style="background: {{ $c }}"></span>@endforeach
                    </div>
                    <p class="mt-2 text-sm font-medium">{{ $t->name }}</p>
                    <p class="text-xs text-stone-600">{{ $t->style_label }}</p>
                </label>
            @endforeach
        </div>
        @error('template_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </fieldset>
    <button @disabled($limitReached) class="rounded-md bg-emerald-900 px-5 py-2.5 font-medium text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50">Simpan sebagai draf</button>
</form>
@endsection

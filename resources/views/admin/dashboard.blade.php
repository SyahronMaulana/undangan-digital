@extends('layouts.dashboard')
@section('title', 'Dasbor Admin')
@section('content')
<h1 class="text-2xl font-semibold">Dasbor Super Admin</h1>
<dl class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach ([['Customer',$stats['customer']],['Undangan',$stats['undangan']],['Undangan terbit',$stats['terbit']],['Pembayaran menunggu',$stats['pembayaran_menunggu']]] as [$label,$value])
        <div class="rounded-lg border border-stone-200 bg-white p-4">
            <dt class="text-sm text-stone-600">{{ $label }}</dt>
            <dd class="mt-1 text-2xl font-semibold">{{ $value }}</dd>
        </div>
    @endforeach
</dl>
@endsection

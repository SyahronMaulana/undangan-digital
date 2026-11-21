{{--
    Tombol "Hubungi Admin via WhatsApp" untuk customer.

    Bila nomor WhatsApp admin belum dikonfigurasi, tautan wa.me tanpa tujuan
    TIDAK dibuat. Sebagai gantinya ditampilkan pemberitahuan.

    Variabel:
    - $url  : tautan wa.me (string) atau null bila nomor admin belum diatur.
    - $note : catatan baku yang tampil bersama tombol (opsional).
--}}
@php
    $url = $url ?? null;
    $note = $note ?? null;
@endphp
@if($url)
    <a href="{{ $url }}" target="_blank" rel="noopener" class="flex w-full items-center justify-center gap-2 rounded-full bg-emerald-600 px-4 py-2.5 text-[11px] font-semibold text-white hover:bg-emerald-700">
        @include('wedding.partials.whatsapp-icon', ['class' => 'h-3.5 w-3.5']) Hubungi Admin via WhatsApp
    </a>
    @if($note)
        <p class="text-[10px] leading-4 text-[#8a938b]">{{ $note }}</p>
    @endif
@else
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-900">
        <p class="font-semibold">Kontak admin belum dikonfigurasi.</p>
        <p class="mt-1">Nomor WhatsApp admin belum diatur. Silakan hubungi admin melalui kanal lain atau coba lagi nanti.</p>
    </div>
@endif

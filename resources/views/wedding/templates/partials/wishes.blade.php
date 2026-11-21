@php $locked = $previewMode ?? false; @endphp
<div class="tw-block tw-wishes">
    @if(session('wish_success'))
        <p class="tw-alert tw-alert-ok">{{ session('wish_success') }}</p>
    @endif

    @if($locked)
        <div class="tw-note">Ucapan pada pratinjau ini adalah contoh. Tamu dapat mengirim ucapan setelah undangan dipublikasikan.</div>
    @endif

    <form method="POST" action="{{ route('wedding.wishes.store', $wedding->slug) }}" class="tw-form" @if($locked) onsubmit="return false" @endif>
        @csrf
        @if($guest)
            <input type="hidden" name="token" value="{{ $guest->token }}">
        @else
            <label class="tw-field">
                <span class="tw-label">Nama Anda</span>
                <input class="tw-input" type="text" name="name" value="{{ old('name') }}" maxlength="100" placeholder="Nama lengkap" @if(!$locked) required @endif>
            </label>
        @endif

        <label class="tw-field">
            <span class="tw-label">Ucapan & doa</span>
            <textarea class="tw-textarea" name="message" rows="3" maxlength="600" placeholder="Tuliskan ucapan dan doa terbaik Anda..." @if(!$locked) required @endif @disabled($locked)>{{ old('message') }}</textarea>
        </label>

        <button class="tw-btn" type="submit" @disabled($locked)>{{ $locked ? 'Pratinjau Ucapan' : 'Kirim Ucapan' }}</button>
    </form>

    <div class="tw-wish-list" data-wish-list>
        @forelse($wedding->wishes as $wish)
            <article class="tw-wish">
                <div class="tw-wish-head">
                    <strong>{{ $wish->name }}</strong>
                    <span>{{ $wish->created_at?->translatedFormat('d M Y') }}</span>
                </div>
                <p>{{ $wish->message }}</p>
            </article>
        @empty
            <p class="tw-empty">Belum ada ucapan. Jadilah yang pertama memberikan doa.</p>
        @endforelse
    </div>
</div>

@php
    $locked = $previewMode ?? false;
    $selected = old('attendance', $myRsvp?->attendance ?? '');
@endphp
<div class="tw-block tw-rsvp">
    @if(session('rsvp_success'))
        <p class="tw-alert tw-alert-ok">{{ session('rsvp_success') }}</p>
    @endif
    @if($errors->any() && $errors->has('attendance'))
        <p class="tw-alert tw-alert-error">{{ $errors->first('attendance') }}</p>
    @endif

    @if($locked)
        <div class="tw-note">Form ini tampil sebagai pratinjau. Setelah undangan dipublikasikan, tamu dapat mengirim konfirmasi kehadiran.</div>
    @endif

    <form method="POST" action="{{ route('wedding.rsvp.store', $wedding->slug) }}" class="tw-form" @if($locked) onsubmit="return false" @endif>
        @csrf
        @if($guest)
            <input type="hidden" name="token" value="{{ $guest->token }}">
        @else
            <label class="tw-field">
                <span class="tw-label">Nama Anda</span>
                <input class="tw-input" type="text" name="name" value="{{ old('name') }}" maxlength="100" placeholder="Nama lengkap" @if(!$locked) required @endif>
            </label>
        @endif

        <fieldset class="tw-choices">
            <legend class="tw-label">Konfirmasi kehadiran</legend>
            @foreach(\App\Models\WeddingRsvp::ATTENDANCE_LABELS as $value => $label)
                <label class="tw-choice">
                    <input type="radio" name="attendance" value="{{ $value }}" @checked($selected === $value) @if(!$locked) required @endif>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </fieldset>

        <label class="tw-field">
            <span class="tw-label">Jumlah orang yang hadir</span>
            <select class="tw-input" name="guests_count" @disabled($locked)>
                @foreach(range(1, 10) as $count)
                    <option value="{{ $count }}" @selected((int) old('guests_count', $myRsvp?->guests_count ?? 1) === $count)>{{ $count }} orang</option>
                @endforeach
            </select>
        </label>

        <label class="tw-field">
            <span class="tw-label">Pesan untuk mempelai <em>(opsional)</em></span>
            <textarea class="tw-textarea" name="message" rows="3" maxlength="1000" placeholder="Tulis doa atau pesan singkat..." @disabled($locked)>{{ old('message', $myRsvp?->message) }}</textarea>
        </label>

        <button class="tw-btn" type="submit" @disabled($locked)>{{ $locked ? 'Pratinjau Konfirmasi' : 'Kirim Konfirmasi' }}</button>
    </form>
</div>

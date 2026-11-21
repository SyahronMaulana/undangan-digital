@php
    $meta = config('wedding.templates.'.$template->slug, []);
    $palette = $template->palette ?: ($meta['palette'] ?? []);
@endphp
<article class="template-card" data-reveal>
    <a href="{{ route('wedding.templates.show', $template->slug) }}" class="template-thumb" aria-label="Lihat desain {{ $template->name }}">
        <span class="template-badge">{{ $template->style_label }}</span>
        <iframe src="{{ route('wedding.templates.preview', $template->slug) }}" title="Pratinjau template {{ $template->name }}" loading="lazy" tabindex="-1"></iframe>
    </a>
    <div class="template-meta">
        <div>
            <h3>{{ $template->name }}</h3>
            <p>{{ $meta['tagline'] ?? $template->tagline() }}</p>
        </div>
        <div class="palette" aria-hidden="true">
            @foreach($palette as $color)
                <i style="background: {{ $color }}"></i>
            @endforeach
        </div>
    </div>
    <div class="template-actions">
        <a href="{{ route('wedding.templates.show', $template->slug) }}" class="text-link">Lihat Desain</a>
        <a href="{{ route('wedding.templates.use', $template->slug) }}" class="text-link gold">Gunakan Template</a>
    </div>
</article>

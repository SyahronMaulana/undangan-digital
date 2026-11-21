<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wedding extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    // Status pengajuan publikasi (alur customer → admin). Tidak ada paket/langganan.
    public const SUBMISSION_DRAFT = 'draft';
    public const SUBMISSION_WAITING_CONFIRMATION = 'menunggu_konfirmasi';
    public const SUBMISSION_WAITING_PAYMENT = 'menunggu_pembayaran';
    public const SUBMISSION_WAITING_VERIFICATION = 'menunggu_verifikasi';
    public const SUBMISSION_PAYMENT_CONFIRMED = 'pembayaran_dikonfirmasi';
    public const SUBMISSION_PUBLISHED = 'dipublikasikan';
    public const SUBMISSION_REVISION = 'revisi';

    // Status pembayaran manual (dikonfirmasi admin setelah benar-benar dicek).
    public const PAYMENT_UNPAID = 'belum_bayar';
    public const PAYMENT_WAITING_VERIFICATION = 'menunggu_verifikasi';
    public const PAYMENT_CONFIRMED = 'dikonfirmasi';

    public const SUBMISSION_LABELS = [
        self::SUBMISSION_DRAFT => 'Draft',
        self::SUBMISSION_WAITING_CONFIRMATION => 'Menunggu Konfirmasi',
        self::SUBMISSION_WAITING_PAYMENT => 'Menunggu Pembayaran',
        self::SUBMISSION_WAITING_VERIFICATION => 'Menunggu Verifikasi',
        self::SUBMISSION_PAYMENT_CONFIRMED => 'Pembayaran Dikonfirmasi',
        self::SUBMISSION_PUBLISHED => 'Dipublikasikan',
        self::SUBMISSION_REVISION => 'Revisi',
    ];

    public const PAYMENT_LABELS = [
        self::PAYMENT_UNPAID => 'Belum Bayar',
        self::PAYMENT_WAITING_VERIFICATION => 'Menunggu Verifikasi',
        self::PAYMENT_CONFIRMED => 'Pembayaran Dikonfirmasi',
    ];

    protected $guarded = ['id', 'user_id'];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'sections' => 'array',
            'design_settings' => 'array',
            'published_at' => 'datetime',
            'submitted_at' => 'datetime',
            'payment_confirmed_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(WeddingPhoto::class)->orderBy('sort_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WeddingEvent::class)->orderBy('sort_order');
    }

    public function stories(): HasMany
    {
        return $this->hasMany(WeddingStory::class)->orderBy('sort_order');
    }

    public function guests(): HasMany
    {
        return $this->hasMany(WeddingGuest::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(WeddingRsvp::class);
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(WeddingWish::class);
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(WeddingGift::class)->orderBy('sort_order');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(BroadcastCampaign::class);
    }

    public function paymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verifier_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    // ---------- Alur pengajuan publikasi ----------

    public function submissionStatus(): string
    {
        return $this->submission_status ?: self::SUBMISSION_DRAFT;
    }

    public function paymentStatus(): string
    {
        return $this->payment_status ?: self::PAYMENT_UNPAID;
    }

    public function submissionStatusLabel(): string
    {
        return self::SUBMISSION_LABELS[$this->submissionStatus()] ?? ucfirst($this->submissionStatus());
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_LABELS[$this->paymentStatus()] ?? ucfirst(str_replace('_', ' ', $this->paymentStatus()));
    }

    /** Customer sudah mengajukan dan belum dipublikasikan. */
    public function hasActiveSubmission(): bool
    {
        return ! in_array($this->submissionStatus(), [self::SUBMISSION_DRAFT, self::SUBMISSION_PUBLISHED], true);
    }

    public function isAwaitingPublication(): bool
    {
        return $this->hasActiveSubmission() && ! $this->isPublished();
    }

    public function canSubmitPublication(): bool
    {
        if ($this->isPublished()) {
            return false;
        }

        return in_array($this->submissionStatus(), [
            self::SUBMISSION_DRAFT,
            self::SUBMISSION_REVISION,
        ], true);
    }

    public function isPaymentConfirmed(): bool
    {
        return $this->paymentStatus() === self::PAYMENT_CONFIRMED;
    }

    /**
     * Daftar masalah data yang menghalangi publikasi. Kosong = siap publikasi.
     *
     * @return array<int, string>
     */
    public function publicationIssues(): array
    {
        $issues = [];

        if (trim((string) $this->groom_name) === '') {
            $issues[] = 'Nama mempelai pria belum diisi.';
        }
        if (trim((string) $this->bride_name) === '') {
            $issues[] = 'Nama mempelai wanita belum diisi.';
        }
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $this->slug)) {
            $issues[] = 'Alamat undangan (slug) tidak valid.';
        }
        if (in_array($this->slug, config('wedding.reserved_slugs', []), true)) {
            $issues[] = 'Alamat undangan memakai kata yang dicadangkan sistem.';
        }
        if ($this->events()->count() === 0) {
            $issues[] = 'Tambahkan minimal satu rangkaian acara.';
        }

        return $issues;
    }

    public function meetsPublicationRequirements(): bool
    {
        return $this->publicationIssues() === [];
    }

    public function markSubmitted(): void
    {
        $this->forceFill([
            'submission_status' => self::SUBMISSION_WAITING_CONFIRMATION,
            'submitted_at' => now(),
            'revision_notes' => null,
        ])->save();
    }

    public function markPaymentWaiting(): void
    {
        $this->forceFill([
            'submission_status' => self::SUBMISSION_WAITING_PAYMENT,
            'payment_status' => self::PAYMENT_UNPAID,
        ])->save();
    }

    /** Customer menandai sudah membayar; admin masih harus memverifikasi. */
    public function markPaymentVerifying(): void
    {
        $this->forceFill([
            'submission_status' => self::SUBMISSION_WAITING_VERIFICATION,
            'payment_status' => self::PAYMENT_WAITING_VERIFICATION,
        ])->save();
    }

    public function confirmPayment(int $adminId): void
    {
        $this->forceFill([
            'submission_status' => self::SUBMISSION_PAYMENT_CONFIRMED,
            'payment_status' => self::PAYMENT_CONFIRMED,
            'payment_confirmed_at' => now(),
            'payment_verifier_id' => $adminId,
        ])->save();
    }

    public function markRevision(string $notes): void
    {
        $this->forceFill([
            'submission_status' => self::SUBMISSION_REVISION,
            'revision_notes' => $notes,
        ])->save();
    }

    public function markPublished(int $adminId): void
    {
        $this->forceFill([
            'status' => self::STATUS_PUBLISHED,
            'submission_status' => self::SUBMISSION_PUBLISHED,
            'published_at' => now(),
            'publisher_id' => $adminId,
        ])->save();
    }

    /**
     * Admin menarik/membatalkan publikasi. Akses publik langsung ditutup karena
     * PublicInvitationController hanya melayani status published.
     */
    public function markUnpublished(?string $notes = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_DRAFT,
            'submission_status' => self::SUBMISSION_PAYMENT_CONFIRMED,
            'published_at' => null,
            'publisher_id' => null,
            'revision_notes' => $notes ?: 'Publikasi ditarik oleh Admin. Hubungi Admin untuk informasi lebih lanjut.',
        ])->save();
    }

    /** Status section digabung dengan nilai bawaan agar section baru tetap punya default. */
    public function sectionEnabled(string $key): bool
    {
        $defaults = config('wedding.default_sections', []);
        $sections = array_merge($defaults, $this->sections ?? []);

        return (bool) ($sections[$key] ?? false);
    }

    public function coupleNames(): string
    {
        return ($this->groom_nickname ?: $this->groom_name).' & '.($this->bride_nickname ?: $this->bride_name);
    }

    public function groomDisplayName(): string
    {
        return $this->groom_nickname ?: $this->groom_name;
    }

    public function brideDisplayName(): string
    {
        return $this->bride_nickname ?: $this->bride_name;
    }

    /** Inisial nama mempelai pria (mis. "Ahmad Fauzi" → "AF"). Selalu ada isinya. */
    public function groomInitials(): string
    {
        return self::initials($this->groom_name);
    }

    /** Inisial nama mempelai wanita (mis. "Siti Rahma" → "SR"). Selalu ada isinya. */
    public function brideInitials(): string
    {
        return self::initials($this->bride_name);
    }

    /**
     * Inisial dari sebuah nama. Dipakai sebagai pengganti foto mempelai bila
     * tidak ada foto. Selalu mengembalikan minimal satu karakter sehingga tidak
     * pernah kosong/"null"/"undefined".
     *
     * Contoh:
     *  - "Ahmad Fauzi"        → "AF"  (kata pertama + kata terakhir)
     *  - "Siti"               → "SI"  (satu kata: dua huruf pertama)
     *  - "R. Ahmad Fauzi"     → "RF"  (lebih dari dua kata: kata pertama + terakhir)
     *  - "  " / null          → "?"   (jaring pengaman)
     */
    public static function initials(?string $name): string
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', (string) $name));

        if ($name === '') {
            return '?';
        }

        $words = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $words = array_values($words);

        if ($words === []) {
            return '?';
        }

        $firstLetter = static function (string $word): string {
            if (preg_match('/[\p{L}\p{N}]/u', $word, $m)) {
                return mb_strtoupper($m[0]);
            }

            return '';
        };

        if (count($words) === 1) {
            $letters = preg_split('//u', $words[0], -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $meaningful = '';
            foreach ($letters as $letter) {
                if (preg_match('/[\p{L}\p{N}]/u', $letter)) {
                    $meaningful .= mb_strtoupper($letter);
                }
                if (mb_strlen($meaningful) >= 2) {
                    break;
                }
            }

            return $meaningful !== '' ? $meaningful : '?';
        }

        $initials = $firstLetter($words[0]).$firstLetter($words[count($words) - 1]);

        return $initials !== '' ? $initials : '?';
    }

    public function groomParents(): string
    {
        return $this->parentsLine($this->groom_father, $this->groom_mother, 'Putra dari');
    }

    public function brideParents(): string
    {
        return $this->parentsLine($this->bride_father, $this->bride_mother, 'Putri dari');
    }

    private function parentsLine(?string $father, ?string $mother, string $prefix): string
    {
        if ($father && $mother) {
            return $prefix.' '.$father.' dan '.$mother;
        }

        if ($father || $mother) {
            return $prefix.' '.($father ?: $mother);
        }

        return $prefix.' keluarga besar kami';
    }

    public function mainEvent(): ?WeddingEvent
    {
        return $this->events->sortBy('sort_order')->first();
    }

    public function invitationUrl(?WeddingGuest $guest = null): string
    {
        $url = route('wedding.invitation.show', $this->slug);
        if ($guest?->token) {
            $url .= '?token='.$guest->token;
        }

        return $url;
    }

    public function coverUrl(): ?string
    {
        return self::assetUrl($this->cover_photo);
    }

    public function groomPhotoUrl(): ?string
    {
        return self::assetUrl($this->groom_photo);
    }

    public function bridePhotoUrl(): ?string
    {
        return self::assetUrl($this->bride_photo);
    }

    public static function assetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, 'wedding-assets/')) {
            return asset($path);
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    public function photoUrls(): array
    {
        return $this->photos
            ->map(fn (WeddingPhoto $photo) => self::assetUrl($photo->path))
            ->filter()
            ->values()
            ->all();
    }

    public function quoteText(): string
    {
        if ($this->quote_text) {
            return $this->quote_text;
        }

        $style = $this->language_style === 'adat_jawa' ? 'jawa' : 'ar-rum-21';
        if ($this->language_style === 'umum') {
            $style = 'umum';
        }

        return config('wedding.quotes.'.$style.'.text', '');
    }

    public function quoteSource(): string
    {
        if ($this->quote_source) {
            return $this->quote_source;
        }

        return config('wedding.quotes.ar-rum-21.source', 'QS. Ar-Rum: 21');
    }

    public function openingText(): string
    {
        return $this->opening_text
            ?: config('wedding.openings.'.$this->language_style, config('wedding.openings.umum'));
    }

    public function closingText(): string
    {
        return $this->closing_text
            ?: 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu.';
    }

    public function rsvpSummary(): array
    {
        return [
            'hadir' => $this->rsvps->where('attendance', WeddingRsvp::HADIR)->count(),
            'tidak_hadir' => $this->rsvps->where('attendance', WeddingRsvp::TIDAK_HADIR)->count(),
            'ragu' => $this->rsvps->where('attendance', WeddingRsvp::RAGU)->count(),
            'total_orang' => (int) $this->rsvps->where('attendance', WeddingRsvp::HADIR)->sum('guests_count'),
        ];
    }
}

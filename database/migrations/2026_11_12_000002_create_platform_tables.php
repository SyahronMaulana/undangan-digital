<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 40)->unique();
            $t->string('name', 60);
            $t->string('style_label', 80);
            $t->text('description')->nullable();
            $t->json('palette')->nullable();
            $t->boolean('is_active')->default(true)->index();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('packages', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 30)->unique();
            $t->string('name', 60);
            $t->unsignedInteger('price')->default(0);
            $t->unsignedSmallInteger('max_invitations')->default(1);
            $t->unsignedInteger('max_guests')->default(50);
            $t->unsignedSmallInteger('max_photos')->default(6);
            $t->boolean('allow_music')->default(false);
            $t->boolean('allow_whatsapp')->default(false);
            $t->boolean('allow_export')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('weddings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('template_id')->constrained('templates');
            $t->string('slug', 60)->unique();
            $t->string('groom_name', 80);
            $t->string('groom_nickname', 40)->nullable();
            $t->string('groom_father', 80)->nullable();
            $t->string('groom_mother', 80)->nullable();
            $t->string('groom_photo')->nullable();
            $t->string('bride_name', 80);
            $t->string('bride_nickname', 40)->nullable();
            $t->string('bride_father', 80)->nullable();
            $t->string('bride_mother', 80)->nullable();
            $t->string('bride_photo')->nullable();
            $t->string('cover_photo')->nullable();
            $t->date('event_date')->nullable();
            $t->text('opening_text')->nullable();
            $t->text('closing_text')->nullable();
            $t->string('language_style', 20)->default('islami');
            $t->string('quote_key', 40)->nullable();
            $t->text('quote_text')->nullable();
            $t->string('quote_source', 120)->nullable();
            $t->string('music_path')->nullable();
            $t->string('music_title', 120)->nullable();
            $t->json('sections')->nullable();
            $t->json('design_settings')->nullable();
            $t->string('status', 20)->default('draft');
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'status']);
        });

        Schema::create('wedding_photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->string('caption', 160)->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('wedding_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $t->string('name', 80);
            $t->date('event_date')->nullable();
            $t->time('start_time')->nullable();
            $t->time('end_time')->nullable();
            $t->string('venue_name', 120)->nullable();
            $t->text('address')->nullable();
            $t->string('maps_url', 500)->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('wedding_stories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $t->string('title', 80);
            $t->text('body')->nullable();
            $t->string('story_date', 40)->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('wedding_guests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);
            $t->string('phone', 20)->nullable();
            $t->string('group_name', 60)->nullable();
            $t->string('token', 64)->unique();
            $t->unsignedSmallInteger('max_pax')->default(2);
            $t->timestamp('opened_at')->nullable();
            $t->timestamps();
            $t->index(['wedding_id', 'name']);
        });

        Schema::create('wedding_rsvps', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $t->foreignId('wedding_guest_id')->nullable()->constrained('wedding_guests')->nullOnDelete();
            $t->string('name', 100);
            $t->string('attendance', 20); // hadir | tidak_hadir | ragu
            $t->unsignedSmallInteger('guests_count')->default(1);
            $t->text('message')->nullable();
            $t->timestamps();
            $t->index(['wedding_id', 'attendance']);
        });

        Schema::create('wedding_wishes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $t->foreignId('wedding_guest_id')->nullable()->constrained('wedding_guests')->nullOnDelete();
            $t->string('name', 100);
            $t->text('message');
            $t->string('status', 20)->default('approved'); // approved | hidden
            $t->timestamps();
            $t->index(['wedding_id', 'status']);
        });

        Schema::create('wedding_gifts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $t->string('bank_name', 60);
            $t->string('account_number', 40);
            $t->string('account_name', 100);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('broadcast_campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);
            $t->text('message_template');
            $t->timestamps();
        });

        Schema::create('broadcast_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('broadcast_campaign_id')->constrained('broadcast_campaigns')->cascadeOnDelete();
            $t->foreignId('wedding_guest_id')->nullable()->constrained('wedding_guests')->nullOnDelete();
            $t->string('phone', 20);
            $t->text('body');
            // dibuat | link_dibuka | dikonfirmasi_manual. Tidak ada status "terkirim" tanpa konfirmasi valid.
            $t->string('status', 30)->default('dibuat');
            $t->timestamp('link_opened_at')->nullable();
            $t->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('package_id')->constrained('packages');
            $t->string('status', 20)->default('aktif');
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $t->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $t->unsignedInteger('amount')->default(0);
            $t->string('method', 30)->default('manual');
            $t->string('status', 20)->default('menunggu'); // menunggu | dikonfirmasi | ditolak
            $t->string('proof_path')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'payments', 'subscriptions', 'broadcast_messages', 'broadcast_campaigns',
            'wedding_gifts', 'wedding_wishes', 'wedding_rsvps', 'wedding_guests',
            'wedding_stories', 'wedding_events', 'wedding_photos', 'weddings',
            'packages', 'templates',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

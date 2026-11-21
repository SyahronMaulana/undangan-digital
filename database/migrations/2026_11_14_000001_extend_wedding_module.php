<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            if (! Schema::hasColumn('weddings', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('status');
            }
            if (! Schema::hasColumn('weddings', 'groom_instagram')) {
                $table->string('groom_instagram', 80)->nullable()->after('groom_photo');
            }
            if (! Schema::hasColumn('weddings', 'bride_instagram')) {
                $table->string('bride_instagram', 80)->nullable()->after('bride_photo');
            }
            if (! Schema::hasColumn('weddings', 'cover_caption')) {
                $table->string('cover_caption', 160)->nullable()->after('cover_photo');
            }
            if (! Schema::hasColumn('weddings', 'dress_code')) {
                $table->string('dress_code', 120)->nullable()->after('closing_text');
            }
            if (! Schema::hasColumn('weddings', 'hashtag')) {
                $table->string('hashtag', 80)->nullable()->after('dress_code');
            }
            if (! Schema::hasColumn('weddings', 'live_stream_url')) {
                $table->string('live_stream_url', 500)->nullable()->after('hashtag');
            }
        });

        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('packages', 'features')) {
                $table->json('features')->nullable()->after('allow_export');
            }
        });

        Schema::table('broadcast_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('broadcast_campaigns', 'status')) {
                $table->string('status', 20)->default('draft')->after('message_template');
            }
            if (! Schema::hasColumn('broadcast_campaigns', 'total_recipients')) {
                $table->unsignedInteger('total_recipients')->default(0)->after('status');
            }
            if (! Schema::hasColumn('broadcast_campaigns', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('total_recipients');
            }
        });

        Schema::table('broadcast_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('broadcast_messages', 'opened_manually_at')) {
                $table->timestamp('opened_manually_at')->nullable()->after('link_opened_at');
            }
        });

        if (! Schema::hasTable('wedding_settings')) {
            Schema::create('wedding_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 80)->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wedding_activity_logs')) {
            Schema::create('wedding_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wedding_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('event', 60);
                $table->string('description', 255)->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['wedding_id', 'event']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_activity_logs');
        Schema::dropIfExists('wedding_settings');

        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->dropColumn('opened_manually_at');
        });
        Schema::table('broadcast_campaigns', function (Blueprint $table) {
            $table->dropColumn(['status', 'total_recipients', 'sent_at']);
        });
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['description', 'features']);
        });
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropColumn([
                'views_count', 'groom_instagram', 'bride_instagram', 'cover_caption',
                'dress_code', 'hashtag', 'live_stream_url',
            ]);
        });
    }
};

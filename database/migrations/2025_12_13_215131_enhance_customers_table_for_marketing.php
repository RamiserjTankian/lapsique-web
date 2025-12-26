<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Añadir WhatsApp separado del teléfono
            $table->string('whatsapp')->nullable()->after('phone');
            
            // Status del lead/customer
            $table->enum('status', ['lead', 'prospect', 'customer', 'inactive'])
                  ->default('lead')
                  ->after('whatsapp');
            
            // Segmentación y metadata
            $table->json('tags')->nullable()->after('status');
            $table->json('metadata')->nullable()->after('tags');
            
            // Preferencias de comunicación
            $table->boolean('subscribed_sms')->default(false)->after('subscribed_newsletter');
            $table->boolean('subscribed_whatsapp')->default(false)->after('subscribed_sms');
            
            // Verificaciones
            $table->timestamp('email_verified_at')->nullable()->after('subscribed_whatsapp');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            
            // Lifecycle y scoring
            $table->enum('lifecycle_stage', ['subscriber', 'lead', 'mql', 'sql', 'customer', 'evangelist'])
                  ->default('subscriber')
                  ->after('phone_verified_at');
            $table->integer('lead_score')->default(0)->after('lifecycle_stage');
            
            // Tracking UTM
            $table->string('utm_source')->nullable()->after('source');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_term')->nullable()->after('utm_campaign');
            $table->string('utm_content')->nullable()->after('utm_term');
            
            // Información técnica
            $table->string('ip_address', 45)->nullable()->after('utm_content');
            $table->text('user_agent')->nullable()->after('ip_address');
            
            // Índices para performance
            $table->index('status');
            $table->index('lifecycle_stage');
            $table->index('lead_score');
            $table->index(['subscribed_newsletter', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['customers_status_index']);
            $table->dropIndex(['customers_lifecycle_stage_index']);
            $table->dropIndex(['customers_lead_score_index']);
            $table->dropIndex(['customers_subscribed_newsletter_status_index']);
            
            $table->dropColumn([
                'whatsapp',
                'status',
                'tags',
                'metadata',
                'subscribed_sms',
                'subscribed_whatsapp',
                'email_verified_at',
                'phone_verified_at',
                'lifecycle_stage',
                'lead_score',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};

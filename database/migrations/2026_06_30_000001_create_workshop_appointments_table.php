<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_appointments', function (Blueprint $table) {
            $table->id();
            $table->string('lead_name');
            $table->string('lead_cpf', 14);
            $table->string('lead_phone', 30);
            $table->string('lead_email')->nullable();
            $table->string('vehicle_brand');
            $table->string('vehicle_model');
            $table->string('vehicle_plate', 12);
            $table->char('vehicle_year', 4)->nullable();
            $table->unsignedInteger('vehicle_current_km')->nullable();
            $table->string('desired_service')->nullable();
            $table->text('message')->nullable();
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->string('status')->default('Novo');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'scheduled_date']);
            $table->index(['scheduled_date', 'scheduled_time']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('appointment_id')
                ->nullable()
                ->after('client_id')
                ->constrained('workshop_appointments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('appointment_id');
        });

        Schema::dropIfExists('workshop_appointments');
    }
};

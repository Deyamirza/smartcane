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
        // 1. Devices Table
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id_device');
            $table->unsignedInteger('id_user');
            $table->string('device_name');
            $table->string('mac_address')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('registered_at')->useCurrent();

            // Foreign Key
            $table->foreign('id_user')
                  ->references('id_user')
                  ->on('users')
                  ->onDelete('cascade');
        });

        // 2. Sensor Logs Table
        Schema::create('sensor_logs', function (Blueprint $table) {
            $table->increments('id_sensor');
            $table->unsignedInteger('id_device');
            $table->float('distance_cm');
            $table->enum('obstacle_detected', ['yes', 'no']);
            $table->timestamp('recorded_at')->useCurrent();

            // Foreign Key
            $table->foreign('id_device')
                  ->references('id_device')
                  ->on('devices')
                  ->onDelete('cascade');
        });

        // 3. GPS Logs Table
        Schema::create('gps_logs', function (Blueprint $table) {
            $table->increments('id_gps');
            $table->unsignedInteger('id_device');
            $table->double('latitude');
            $table->double('longitude');
            $table->float('accuracy_m')->default(0);
            $table->timestamp('recorded_at')->useCurrent();

            // Foreign Key
            $table->foreign('id_device')
                  ->references('id_device')
                  ->on('devices')
                  ->onDelete('cascade');
        });

        // 4. SOS Events Table
        Schema::create('sos_events', function (Blueprint $table) {
            $table->increments('id_sos');
            $table->unsignedInteger('id_device');
            $table->double('latitude');
            $table->double('longitude');
            $table->enum('status', ['active', 'acknowledged', 'resolved'])->default('active');
            $table->string('telegram_message_id')->nullable();
            $table->timestamp('triggered_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();

            // Foreign Key
            $table->foreign('id_device')
                  ->references('id_device')
                  ->on('devices')
                  ->onDelete('cascade');
        });

        // 5. Notifications Table
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id_notif');
            $table->unsignedInteger('id_sos');
            $table->unsignedInteger('id_user');
            $table->string('telegram_chat_id');
            $table->enum('delivery_status', ['sent', 'failed', 'pending'])->default('pending');
            $table->timestamp('sent_at')->useCurrent();

            // Foreign Keys
            $table->foreign('id_sos')
                  ->references('id_sos')
                  ->on('sos_events')
                  ->onDelete('cascade');

            $table->foreign('id_user')
                  ->references('id_user')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('sos_events');
        Schema::dropIfExists('gps_logs');
        Schema::dropIfExists('sensor_logs');
        Schema::dropIfExists('devices');
    }
};

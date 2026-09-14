<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            // Path to uploaded audio call recording (mp3/wav/ogg/m4a/webm)
            $table->string('audio_recording_path')->nullable()->after('notes');
            // Original filename shown to user
            $table->string('audio_recording_name')->nullable()->after('audio_recording_path');
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn(['audio_recording_path', 'audio_recording_name']);
        });
    }
};


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
        Schema::create('patient_cases', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid');
            $table->string('name');
            $table->string('email');
            $table->longText('extraction');
            $table->longText('attachments');
            $table->string('case_id');
            $table->string('age');
            $table->string('gender');
            $table->string('ipr');
            $table->longText('chief_complaint');
            $table->longText('treatment_plan');
            $table->string('stl_upper_file')->nullable();
            $table->string('stl_lower_file')->nullable();
            $table->string('stl_byte_scan_file')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedBigInteger('assign_to')->default(0);
            $table->unsignedBigInteger('created_by_admin')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_cases');
    }
};

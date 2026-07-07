<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_image_reviews', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('image_type', [
                'cover_img',
                'cover_img2',
                'cover_img3',
            ]);

            $table->string('image_path');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->integer('risk_score')
                ->default(0);

            $table->json('moderation_labels')
                ->nullable();

            $table->string('ai_provider')
                ->default('aws_rekognition');

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'product_image_reviews'
        );
    }
};

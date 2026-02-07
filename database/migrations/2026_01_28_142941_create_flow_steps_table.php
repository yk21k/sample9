<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flow_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('flow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('step_order'); // 並び順
            $table->string('title');               // 手順名
            $table->text('description')->nullable(); // 説明文
            $table->boolean('is_required')->default(false);

            $table->timestamps();

            $table->unique(['flow_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flow_steps');
    }
};


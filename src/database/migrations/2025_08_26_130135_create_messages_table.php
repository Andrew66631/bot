<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dialog_id')->constrained()->onDelete('cascade');
            $table->bigInteger('telegram_message_id')->comment('ID сообщения в Telegram');
            $table->text('message')->nullable()->comment('Текст сообщения');
            $table->boolean('is_outgoing')->default(false)->comment('Исходящее сообщение');
            $table->timestamp('message_date')->comment('Дата сообщения в Telegram');
            $table->timestamps();

            $table->index('dialog_id');
            $table->index('telegram_message_id');
            $table->index('is_outgoing');
            $table->index('message_date');

            $table->unique(['dialog_id', 'telegram_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

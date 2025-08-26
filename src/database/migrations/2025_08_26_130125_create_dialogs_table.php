<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dialogs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique()->comment('ID диалога в Telegram');
            $table->string('name')->nullable()->comment('Название диалога');
            $table->string('type')->default('private')->comment('Тип диалога: private, group, channel');
            $table->string('username')->nullable()->comment('Username пользователя/чата');
            $table->text('last_message')->nullable()->comment('Последнее сообщение');
            $table->timestamp('last_message_date')->nullable()->comment('Дата последнего сообщения');
            $table->timestamps();

            $table->index('telegram_id');
            $table->index('type');
            $table->index('username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dialogs');
    }
};

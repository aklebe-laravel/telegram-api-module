<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('telegram_identities')) {
            Schema::create('telegram_identities', function (Blueprint $table) {
                $table->id();
                $table->string('telegram_id', 100)->nullable();
                // $table->string('bot_token', 255)
                //     ->nullable()
                //     ->comment('When set, telegram api will update all bot infos incl. chats and channels automatically');
                $table->boolean('is_enabled')->default(true)->comment('True when enabled and selectable');
                $table->boolean('is_bot')->default(false)->comment('true if its a bot');
                $table->string('type', 255)
                    ->nullable()
                    ->comment('Telegram identity type like channel, group or just empty');
                $table->string('display_name', 255)
                    ->nullable()
                    ->comment('Display name: first_name for users and title for chat and groups');
                $table->string('username', 255)->unique()->nullable()->comment('Telegram username');
                $table->string('language_code', 10)->nullable()->comment('like end or de');
                $table->integer('position')->default(1000)->comment('Position to sort');
                $table->Text('additional_data')->nullable()->comment('json data');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('telegram_identity_findings')) {
            Schema::create('telegram_identity_findings', function (Blueprint $table) {
                $table->unsignedBigInteger('entity_id')->unsigned();
                $table->unsignedBigInteger('finder_id')->unsigned();

                $table->unique(['entity_id', 'finder_id']);
                $table->foreign('entity_id')
                    ->references('id')
                    ->on('telegram_identities')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('finder_id')
                    ->references('id')
                    ->on('telegram_identities')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telegram_identity_findings');
        Schema::dropIfExists('telegram_identities');
    }

};

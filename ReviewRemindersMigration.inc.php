<?php

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReviewRemindersMigration extends Migration {
    public function up() {
        Manager::schema()->create("review_reminders", function (Blueprint $table) {
            $table->unsignedInteger("id")->autoIncrement()->primary();
            $table->bigInteger("contextId");
            $table->integer("days");
            $table->enum("beforeOrAfter", ["before", "after"]);
            $table->enum("deadline", ["response", "review"]);
            $table->text("templateId");
            $table->foreign("contextId")->references("journal_id")->on("journals")->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::drop("review_reminders");
    }
}
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEndsAtToSubscriptionObjects extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'ends_at')) {
                //
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->timestamp('ends_at');
                });
            }
        }
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'ends_at')) {
                //
            } else {
                Schema::table('products', function (Blueprint $table) {
                    $table->timestamp('ends_at');
                });
            }
        }
        if (Schema::hasTable('merchants')) {
            if (Schema::hasColumn('merchants', 'ends_at')) {
                //
            } else {
                Schema::table('merchants', function (Blueprint $table) {
                    $table->timestamp('ends_at');
                });
            } 
        }
        if (Schema::hasTable('reports')) {
            if (Schema::hasColumn('reports', 'ends_at')) {
                //
            } else {
                Schema::table('reports', function (Blueprint $table) {
                    $table->timestamp('ends_at');
                });
            }
        }
        if (Schema::hasTable('groups')) {
            if (Schema::hasColumn('groups', 'ends_at')) {
                //
            } else {
                Schema::table('groups', function (Blueprint $table) {
                    $table->timestamp('ends_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'ends_at')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('ends_at');
                });
            }
        }
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'ends_at')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropColumn('ends_at');
                });
            }
        }
        if (Schema::hasTable('merchants')) {
            if (Schema::hasColumn('merchants', 'ends_at')) {
                Schema::table('merchants', function (Blueprint $table) {
                    $table->dropColumn('ends_at');
                });
            }
        }
        if (Schema::hasTable('reports')) { 
            if (Schema::hasColumn('reports', 'ends_at')) {
                Schema::table('reports', function (Blueprint $table) {
                    $table->dropColumn('ends_at');
                });
            }
        }
        if (Schema::hasTable('groups')) {
            if (Schema::hasColumn('groups', 'ends_at')) {
                Schema::table('groups', function (Blueprint $table) {
                    $table->dropColumn('ends_at');
                });
            }
        }
    }

}

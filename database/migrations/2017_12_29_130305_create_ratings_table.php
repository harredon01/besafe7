<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatingsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('object_id')->unsigned()->nullable();
            $table->index('object_id');
            $table->text('comment');
            $table->double('rating');
            $table->boolean('is_report')->default(false);
            $table->string('type')->nullable();
            $table->string('pseudonim')->nullable();
            $table->foreign('user_id')->references('id')
                    ->on('users');
            $table->timestamps();
        });
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'rating')) {
                //
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->double('rating')->nullable();
                    $table->integer('rating_count')->unsigned()->nullable();
                });
            }
        }
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'rating')) {
                //
            } else {
                Schema::table('products', function (Blueprint $table) {
                    $table->double('rating')->nullable();
                    $table->integer('rating_count')->unsigned()->nullable();
                });
            }
        }
        if (Schema::hasTable('merchants')) {
            if (Schema::hasColumn('merchants', 'rating')) {
                //
            } else {
                Schema::table('merchants', function (Blueprint $table) {
                    $table->double('rating')->nullable();
                    $table->integer('rating_count')->unsigned()->nullable();
                });
            }
        }
        if (Schema::hasTable('reports')) {
            if (Schema::hasColumn('reports', 'rating')) {
                //
            } else {
                Schema::table('reports', function (Blueprint $table) {
                    $table->double('rating')->nullable();
                    $table->integer('rating_count')->unsigned()->nullable();
                });
            }
        }
        if (Schema::hasTable('groups')) {
            if (Schema::hasColumn('groups', 'rating')) {
                //
            } else {
                Schema::table('groups', function (Blueprint $table) {
                    $table->double('rating')->nullable();
                    $table->integer('rating_count')->unsigned()->nullable();
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
        Schema::dropIfExists('ratings');
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'rating')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('rating');
                    $table->dropColumn('rating_count');
                });
            }
        }
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'rating')) {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropColumn('rating');
                    $table->dropColumn('rating_count');
                });
            }
        }
        if (Schema::hasTable('merchants')) {
            if (Schema::hasColumn('merchants', 'rating')) {
                Schema::table('merchants', function (Blueprint $table) {
                    $table->dropColumn('rating');
                    $table->dropColumn('rating_count');
                });
            }
        }
        if (Schema::hasTable('reports')) {
            if (Schema::hasColumn('reports', 'rating')) {
                Schema::table('reports', function (Blueprint $table) {
                    $table->dropColumn('rating');
                    $table->dropColumn('rating_count');
                });
            }
        }
        if (Schema::hasTable('groups')) {
            if (Schema::hasColumn('groups', 'rating')) {
                Schema::table('groups', function (Blueprint $table) {
                    $table->dropColumn('rating');
                    $table->dropColumn('rating_count');
                });
            }
        } 
    }

}

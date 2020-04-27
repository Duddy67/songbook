<?php namespace Codalia\SongBook\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSongsTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_songbook_songs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
	    //$table->integer('user_id')->unsigned()->nullable()->index();
	    $table->string('title')->nullable();
            $table->string('slug')->index();
            $table->text('lyrics')->nullable();
            $table->text('notes')->nullable();
            $table->char('status', 15)->default('unpublished');
	    $table->integer('category_id')->unsigned()->nullable()->index();
	    $table->integer('access_id')->unsigned()->nullable()->index();
	    $table->integer('created_by')->unsigned()->nullable()->index();
	    $table->integer('updated_by')->unsigned();
	    $table->timestamp('published_up')->nullable();
	    $table->timestamp('published_down')->nullable();
	    $table->integer('sort_order');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_songbook_songs');
    }
}

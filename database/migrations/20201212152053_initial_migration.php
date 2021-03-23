<?php
declare(strict_types=1);

use SubLand\Migrations\Migration;

final class InitialMigration extends Migration
{
    public function up()
    {
        // Create Users table if not exists
        if (!$this->schema->hasTable('users')){
            $this->schema->create('users', function(Illuminate\Database\Schema\Blueprint $table){
                $table->unsignedBigInteger('user_id')->primary();
                $table->string('language',15)->default('farsi_persian');
                $table->string('local_language',3)->default('en');
                $table->timestamps();
            });
        }

        // Create Queries table
        $this->schema->create('search_queries', function(Illuminate\Database\Schema\Blueprint $table){
            $table->bigIncrements('query_id');
            $table->string('query')->unique();
            $table->timestamps();
        });

        // Create Films table
        $this->schema->create('films', function(Illuminate\Database\Schema\Blueprint $table){
            $table->bigIncrements('film_id');
            $table->string('title',1000);
            $table->string('url',500)->unique();
            $table->year('year')->nullable();
            $table->string('poster',500)->nullable();
            $table->string('imdb',500)->nullable();
            $table->timestamps();
        });

        // Create Film-Query pivot table
        $this->schema->create('film_query', function(Illuminate\Database\Schema\Blueprint $table){
            $table->bigIncrements('result_id');
            $table->unsignedBigInteger('query_id');
            $table->unsignedBigInteger('film_id');
            $table->timestamps();

            $table->foreign('query_id')->references('query_id')->on('search_queries');
            $table->foreign('film_id')->references('film_id')->on('films');
        });

        // Create Subtitles table
        $this->schema->create('subtitles', function(Illuminate\Database\Schema\Blueprint $table){
            $table->bigIncrements('subtitle_id');
            $table->unsignedBigInteger('film_id');
            $table->string('url',500)->unique();
            $table->string('language',50)->default('farsi_persian');
            $table->string('download_url',500)->unique()->nullable();
            $table->string('author_name',50)->nullable();
            $table->string('author_url',100)->nullable();
            $table->string('extra')->default('');
            $table->text('info')->default('');
            $table->text('preview')->default('');
            $table->text('comment')->default('');
            $table->text('details')->default('');
            $table->dateTime('release_at')->nullable();
            $table->timestamps();

            $table->foreign('film_id')->references('film_id')->on('films');
        });
    }

    public function down()
    {
        $this->schema->drop('subtitles');
        $this->schema->drop('film_query');
        $this->schema->drop('films');
        $this->schema->drop('search_queries');
//        $this->schema->drop('users');
    }
}

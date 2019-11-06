<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxCodeTaxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_code_tax', function (Blueprint $table) {
            $table->unsignedInteger('tax_code_id');
            $table->unsignedInteger('tax_id');
            $table->unsignedDecimal('percentage',5,2);
            $table->foreign('tax_code_id')->references('id')->on('tax_codes')->onDelete('CASCADE')->onUpdate('cascade');
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('CASCADE')->onUpdate('cascade');
            $table->unique(["tax_code_id","tax_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('tax_code_tax');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHondaFieldsInTaxCodeMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tax_codes', function (Blueprint $table) {
            $table->dropForeign('tax_codes_company_id_foreign');
            $table->dropUnique('tax_codes_company_id_code_unique');

            $table->unsignedInteger('part_type_id')->nullable()->after('type_id');
            $table->unsignedInteger('business_id')->nullable()->after('part_type_id');
            $table->text('description')->nullable()->after('business_id');
            
            $table->foreign('part_type_id')->references('id')->on('honda_ro_types')->onDelete('SET NULL')->onUpdate('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('SET NULL')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_codes', function (Blueprint $table) {
            $table->unique(['company_id','code']);

            $table->dropForeign('tax_codes_part_type_id_foreign');
            $table->dropColumn('part_type_id');
            $table->dropForeign('tax_codes_business_id_foreign');
            $table->dropColumn('business_id');
            $table->dropColumn('description');
        });
    }
}

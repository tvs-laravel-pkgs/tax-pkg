<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTaxCodeTax extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('tax_code_tax', function (Blueprint $table) {
			$table->unsignedInteger('state_id')->nullable()->after('percentage');
			$table->foreign('state_id')->references('id')->on('states')->onDelete('cascade')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('tax_code_tax', function (Blueprint $table) {
			$table->dropForeign('tax_code_tax_state_id_foreign');
			$table->dropColumn('state_id');
		});
	}
}

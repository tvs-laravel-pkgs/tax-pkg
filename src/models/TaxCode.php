<?php

namespace Abs\TaxPkg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxCode extends Model {
	use SoftDeletes;
	protected $table = 'taxe_codes';
	protected $fillable = [
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

}

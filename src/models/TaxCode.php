<?php

namespace Abs\TaxPkg;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxCode extends Model {
	use SoftDeletes;
	protected $table = 'tax_codes';
	protected $fillable = [
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

	public function taxes() {
		return $this->belongsToMany('Abs\TaxPkg\Tax', 'tax_code_tax', 'tax_code_id', 'tax_id')->withpivot(['percentage', 'state_id']);
	}

	public static function createFromCollection($records, $company = null) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company) {
					continue;
				}
				$record = self::createFromObject($record_data, $company);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}
	public static function createFromObject($record_data, $company = null) {

		$errors = [];
		if (!$company) {
			$company = Company::where('code', $record_data->company)->first();
		}
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$type = Config::where('name', $record_data->type)->where('config_type_id', 82)->first();
		if (!$type) {
			$errors[] = 'Invalid type : ' . $record_data->type;
		}

		$tax = Tax::where('name', $record_data->tax)->where('company_id', $company->id)->first();
		if (!$tax) {
			$errors[] = 'Invalid tax : ' . $record_data->tax;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'code' => $record_data->code,
		]);
		$record->type_id = $type->id;
		$record->created_by_id = $admin->id;
		$record->save();

		$record->taxes()->syncWithoutDetaching([
			$tax->id => [
				'percentage' => $record_data->percentage,
			],
		]);
		return $record;
	}

}

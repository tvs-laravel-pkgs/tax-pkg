<?php

namespace Abs\TaxPkg;
use Abs\BasicPkg\BaseModel;
use App\Company;
use App\Config;
use App\State;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxCode extends BaseModel {
	use SoftDeletes;
	protected $table = 'tax_codes';
	protected $fillable = [
		'company_id',
		'type_id',
		'name',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

	// Relationships --------------------------------------------------------------

	public static function relationships($action = '') {
		$relationships = [
			'taxes',
		];

		return $relationships;
	}

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

	public static function searchSacCode($r) {
		$key = $r->key;
		$list = self::where('company_id', Auth::user()->company_id)
			->select(
				'id',
				'code'
			)
			->where(function ($q) use ($key) {
				$q->orWhere('code', 'like', '%' . $key . '%')
				;
			})
			->get();
		return response()->json($list);
	}
	public static function getList($params = [], $add_default = true, $default_text = 'Select Tax Code') {
		$list = Collect(Self::select([
			'id',
			'code as name',
		])
				->orderBy('name')
				->get());
		if ($add_default) {
			$list->prepend(['id' => '', 'name' => $default_text]);
		}
		return $list;
	}

	public static function mapTaxes($records) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->tax_name) {
					continue;
				}
				$record = self::mapTax($record_data);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}

	public static function mapTax($record_data) {
		$errors = [];
		$tax = Tax::where('name', $record_data->tax_name)->first();
		if (!$tax) {
			$errors[] = 'Invalid Tax name : ' . $record_data->tax_name;
		}

		$tax_code = TaxCode::where('code', $record_data->code)->first();
		if (!$tax_code) {
			$errors[] = 'Invalid Tax code : ' . $record_data->code;
		}

		if (!empty($record_data->state_code)) {
			$state = State::where(['code', $record_data->state_code])->first();
			$state_id = $state->id;
		} else {
			$state_id = null;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		// $tax->taxCodes()->syncWithoutDetaching([$tax_code->id]);
		$tax->taxCodes()->syncWithoutDetaching([
			$tax_code->id => [
				'percentage' => $record_data->percentage,
				'state_id' => $state_id,
			],
		]);

		// return $role;
	}

}

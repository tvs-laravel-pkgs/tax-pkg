<?php

namespace Abs\TaxPkg;

use Abs\BasicPkg\BaseModel;
use Abs\HelperPkg\Traits\SeederTrait;
use Abs\ServiceInvoicePkg\ServiceItem;
use App\Company;
use App\Config;
use App\Customer;
use App\Outlet;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends BaseModel {
	use SoftDeletes;
	use SeederTrait;
	protected $table = 'taxes';
	protected $fillable = [
		'company_id',
		'type_id',
		'code',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

	protected static $excelColumnRules = [
		'Tax Name' => [
			'table_column_name' => 'name',
			'rules' => [
				'required' => [
				],
			],
		],
		'Type Name' => [
			'table_column_name' => 'type_id',
			'rules' => [
				'fk' => [
					'class' => 'App\Config',
					'foreign_table_column' => 'name',
				],
			],
		],

	];
	// Relations --------------------------------------------------------------

	public function serviceInvoiceItems() {
		return $this->belongsToMany('Abs\ServiceInvoicePkg\ServiceInvoiceItem', 'service_invoice_item_tax');
	}

	public function taxCodes() {
		return $this->belongsToMany('Abs\TaxPkg\TaxCode', 'tax_code_tax', 'tax_id', 'tax_code_id');
	}

	public static function saveFromObject($record_data) {
		$record = [
			'Company Code' => $record_data->company_code,
			'Tax Name' => $record_data->tax_name,
			'Type Name' => $record_data->type_name,
		];
		return static::saveFromExcelArray($record);
	}

	public static function saveFromExcelArray($record_data) {
		$errors = [];
		$company = Company::where('code', $record_data['Company Code'])->first();
		if (!$company) {
			return [
				'success' => false,
				'errors' => ['Invalid Company : ' . $record_data['Company Code']],
			];
		}

		if (!isset($record_data['created_by_id'])) {
			$admin = $company->admin();

			if (!$admin) {
				return [
					'success' => false,
					'errors' => ['Default Admin user not found'],
				];
			}
			$created_by_id = $admin->id;
		} else {
			$created_by_id = $record_data['created_by_id'];
		}

		$type_id = null;
		if (!empty($record_data['Type Name'])) {
			$type = Config::where([
				'config_type_id' => 89,
				'name' => $record_data['Type Name'],
			])->first();

			if (!$type) {
				$errors[] = 'Invalid Type Name : ' . $record_data['Type Name'];
			} else {
				$type_id = $type->id;
			}
		}

		if (count($errors) > 0) {
			return [
				'success' => false,
				'errors' => $errors,
			];
		}

		$record = Self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data['Tax Name'],
		]);
		$result = Self::validateAndFillExcelColumns($record_data, Static::$excelColumnRules, $record);
		if (!$result['success']) {
			return $result;
		}

		$record->type_id = $type_id;
		$record->company_id = $company->id;
		$record->created_by_id = $created_by_id;
		$record->save();
		return [
			'success' => true,
		];
	}

	public static function getTaxes($service_item_id, $branch_id, $customer_id) {
		$response = array();
		$serviceItem = ServiceItem::find($service_item_id);
		if (!$serviceItem) {
			$response['success'] = false;
			$response['error'] = 'Service Item not found';
			return $response;
		}

		$branch = Outlet::find($branch_id);
		if (!$branch) {
			$response['success'] = false;
			$response['error'] = 'Branch not found';
			return $response;
		}
		if (!$branch->state_id) {
			$response['success'] = false;
			$response['error'] = 'No state informations available for branch';
			return $response;
		}

		$customer = Customer::find($customer_id);
		if (!$customer) {
			$response['success'] = false;
			$response['error'] = 'Customer not found';
			return $response;
		}

		if (!$customer->primaryAddress || !$customer->primaryAddress->state_id) {
			$response['success'] = false;
			$response['error'] = 'No state informations available for customer';
			return $response;
		}

		$branch_state_id = $branch->state_id ? $branch->state_id : null;
		$customer_state_id = ($customer->primaryAddress ? ($customer->primaryAddress->state_id ? $customer->primaryAddress->state_id : null) : null);

		if ($branch_state_id && $customer_state_id) {
			//WITHIN STATE && STATE SPECIFIC(IF CUSTOMER STATE MATCHES)
			if ($branch_state_id == $customer_state_id) {
				$general_taxes = self::where('type_id', 1160)->pluck('id')->toArray();
			} else {
				//INTER STATE && STATE SPECIFIC(IF CUSTOMER STATE MATCHES)
				$general_taxes = self::where('type_id', 1161)->pluck('id')->toArray();
			}
			$statespec_tax = self::where('type_id', 1162)->first();
			if ($statespec_tax && $serviceItem->taxCode) {
				$state_specifi_tax = $serviceItem->taxCode->taxes()->where('state_id', $customer_state_id)->pluck('tax_id')->toArray();
			} else {
				$state_specifi_tax = [];
			}
			$taxes = array_unique(array_merge($general_taxes, $state_specifi_tax));
			// $taxes = $general_taxes;

		} else {
			$taxes = [];
		}

		$response['success'] = true;
		$response['tax_ids'] = $taxes;
		return $response;
	}

	/*public static function createFromCollection($records, $company = null) {
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

			$type = Config::where('name', $record_data->type)->where('config_type_id', 89)->first();
			if (!$type) {
				$errors[] = 'Invalid Tax Type : ' . $record_data->type;
			}

			if (count($errors) > 0) {
				dump($errors);
				return;
			}

			$record = self::firstOrNew([
				'company_id' => $company->id,
				'name' => $record_data->tax_name,
			]);
			$record->type_id = $type->id;
			$record->created_by_id = $admin->id;
			$record->save();
			return $record;
	*/

}

<?php

namespace Abs\TaxPkg;

use Abs\ServiceInvoicePkg\ServiceItem;
use App\Company;
use App\Config;
use App\Customer;
use App\Outlet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model {
	use SoftDeletes;
	protected $table = 'taxes';
	protected $fillable = [
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

	public function serviceInvoiceItems() {
		return $this->belongsToMany('Abs\ServiceInvoicePkg\ServiceInvoiceItem', 'service_invoice_item_tax');
	}

	public function taxCodes() {
		return $this->belongsToMany('Abs\TaxPkg\TaxCode', 'tax_code_tax', 'tax_id', 'tax_code_id');
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

		} else {
			$taxes = [];
		}

		$response['success'] = true;
		$response['tax_ids'] = $taxes;
		return $response;
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
	}

}

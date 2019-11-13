<?php

namespace Abs\TaxPkg;
use Abs\TaxPkg\TaxCode;
use App\Config;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class TaxCodeController extends Controller {

	public function __construct() {
	}

	public function getTaxCodeList() {
		$tax_list = TaxCode::withTrashed()
			->select(
				'taxes.id',
				'taxes.name as name',
				'configs.name as type',
				DB::raw('IF((taxes.deleted_at) IS NULL,"Active","Inactive") as status')
			)
			->join('configs', 'configs.id', 'taxes.type_id')
			->where('taxes.company_id', Auth::user()->company_id)
			->orderby('taxes.id', 'desc');

		return Datatables::of($tax_list)
			->addColumn('name', function ($tax_list) {
				$status = $tax_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $tax_list->name;
			})
			->addColumn('action', function ($tax_list) {
				$edit_img = asset('public/theme/img/table/cndn/edit.svg');
				$delete_img = asset('public/theme/img/table/cndn/delete.svg');
				return '
					<a href="#!/tax-pkg/tax/edit/' . $tax_list->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_tax"
					onclick="angular.element(this).scope().deleteTax(' . $tax_list->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getTaxCodeFormData($id = NULL) {
		if (!$id) {
			$tax_code = new TaxCode;
			$action = 'Add';
		} else {
			$tax_code = TaxCode::withTrashed()->with([
				'taxes',
			])->find($id);
			$action = 'Edit';
		}
		$this->data['type_list'] = Collect(Config::getTaxList()->prepend(['id' => '', 'name' => 'Select Type']));
		$this->data['taxcode_type_list'] = Collect(Config::getTaxCodeTypeList()->prepend(['id' => '', 'name' => 'Select Type']));
		$this->data['tax_list'] = collect(Tax::select('name', 'id')->where('company_id', Auth::user()->company_id)->get()->prepend(['id' => '', 'name' => 'Select Tax']));
		$this->data['tax_code'] = $tax_code;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveTaxCode(Request $request) {
		dd($request->all());
		try {
			if (!empty($request->tax)) {
				foreach ($request->tax as $taxes) {
					$error_messages = [
						'name.required' => 'Tax Name is Required',
						'name.unique' => 'Tax Name:' . $taxes['name'] . ' is already taken',
					];
					$validator = Validator::make($taxes, [
						'name' => [
							'required',
							'unique:taxes,name,' . $taxes['id'] . ',id,company_id,' . Auth::user()->company_id,
						],
					], $error_messages);
					if ($validator->fails()) {
						return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
					}
				}
			}

			DB::beginTransaction();
			if (!empty($request->tax_removal_id)) {
				$tax_removal_id = json_decode($request->tax_removal_id, true);
				Tax::whereIn('id', $tax_removal_id)->delete();
			}
			foreach ($request->tax as $taxes) {
				if (!$taxes['id']) {
					$tax = new Tax;
					$tax->created_by_id = Auth::user()->id;
					$tax->created_at = Carbon::now();
					$tax->updated_at = NULL;
				} else {
					$tax = Tax::withTrashed()->find($taxes['id']);
					$tax->updated_by_id = Auth::user()->id;
					$tax->updated_at = Carbon::now();
				}
				$tax->name = $taxes['name'];
				$tax->company_id = Auth::user()->company_id;
				$tax->type_id = $taxes['type_id'];
				if ($taxes['status'] == 'Inactive') {
					$tax->deleted_at = Carbon::now();
					$tax->deleted_by_id = Auth::user()->id;
				} else {
					$tax->deleted_by_id = NULL;
					$tax->deleted_at = NULL;
				}
				$tax->save();
			}
			DB::commit();
			foreach ($request->tax as $taxes) {
				if (empty($taxes['id'])) {
					return response()->json(['success' => true, 'message' => ['Taxes Added Successfully']]);
				} else {
					return response()->json(['success' => true, 'message' => ['Tax Updated Successfully']]);
				}
			}

		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteTaxCode($id) {
		$delete_status = Tax::where('id', $id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}

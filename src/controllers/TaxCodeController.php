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
		$tax_code_list = TaxCode::withTrashed()
			->select(
				'tax_codes.id',
				'tax_codes.code as name',
				'configs.name as type',
				DB::raw('IF((tax_codes.deleted_at) IS NULL,"Active","Inactive") as status')
			)
			->join('configs', 'configs.id', 'tax_codes.type_id')
			->where('tax_codes.company_id', Auth::user()->company_id)
			->orderby('tax_codes.id', 'desc');

		return Datatables::of($tax_code_list)
			->addColumn('name', function ($tax_code_list) {
				$status = $tax_code_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $tax_code_list->name;
			})
			->addColumn('cgst', function ($tax_code_list) {
				$get_cgst = DB::table('tax_code_tax')->select('percentage')
					->leftJoin('taxes', 'taxes.id', '=', 'tax_code_tax.tax_id')
					->where('tax_code_id', $tax_code_list->id)
					->where('taxes.name', 'LIKE', '%CGST%')
					->first();
				if (!empty($get_cgst)) {
					return intval($get_cgst->percentage);
				} else {
					return '--';
				}
			})
			->addColumn('sgst', function ($tax_code_list) {
				$get_sgst = DB::table('tax_code_tax')->select('percentage')
					->leftJoin('taxes', 'taxes.id', '=', 'tax_code_tax.tax_id')
					->where('tax_code_id', $tax_code_list->id)
					->where('taxes.name', 'LIKE', '%SGST%')
					->first();
				if (!empty($get_sgst)) {
					return intval($get_sgst->percentage);
				} else {
					return '--';
				}
			})
			->addColumn('igst', function ($tax_code_list) {
				$get_igst = DB::table('tax_code_tax')->select('percentage')
					->leftJoin('taxes', 'taxes.id', '=', 'tax_code_tax.tax_id')
					->where('tax_code_id', $tax_code_list->id)
					->where('taxes.name', 'LIKE', '%IGST%')
					->first();
				if (!empty($get_igst)) {
					return intval($get_igst->percentage);
				} else {
					return '--';
				}
			})
			->addColumn('action', function ($tax_code_list) {
				$edit_img = asset('public/theme/img/table/cndn/edit.svg');
				$delete_img = asset('public/theme/img/table/cndn/delete.svg');
				return '
					<a href="#!/tax-pkg/tax-code/edit/' . $tax_code_list->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_tax_code"
					onclick="angular.element(this).scope().deleteTaxCode(' . $tax_code_list->id . ')" dusk = "delete-btn" title="Delete">
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

	public function getTaxType($id) {
		$get_type = Tax::select(
			'taxes.id',
			'configs.name as type'
		)
			->join('configs', 'configs.id', 'taxes.type_id')
			->where('taxes.id', $id)
			->first();
		return response()->json($get_type);
	}

	public function saveTaxCode(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Tax Code is Required',
				'code.unique' => 'Tax Code is already taken',
				'type_id.required' => 'Type is Required',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required',
					'unique:tax_codes,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'type_id' => 'required',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			if (!empty($request->tax) && $request->tax) {
				$tax_code_taxes = array_column($request->tax, 'tax_id');
				$tax_code_unique = array_unique($tax_code_taxes);
				if (count($tax_code_taxes) != count($tax_code_unique)) {
					return response()->json(['success' => false, 'errors' => ['Tax Code is already taken']]);
				}
			} else {
				return response()->json(['success' => false, 'errors' => ['Taxes is empty']]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$tax_code = new TaxCode;
				$tax_code->created_by_id = Auth::user()->id;
				$tax_code->created_at = Carbon::now();
				$tax_code->updated_at = NULL;
			} else {
				$tax_code = TaxCode::withTrashed()->find($request->id);
				$tax_code->updated_by_id = Auth::user()->id;
				$tax_code->updated_at = Carbon::now();
			}
			$tax_code->code = $request->code;
			$tax_code->type_id = $request->type_id;
			$tax_code->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$tax_code->deleted_at = Carbon::now();
				$tax_code->deleted_by_id = Auth::user()->id;
			} else {
				$tax_code->deleted_by_id = NULL;
				$tax_code->deleted_at = NULL;
			}
			$tax_code->save();

			if (count($request->tax) > 0) {
				$tax_code->taxes()->sync([]);
				foreach ($request->tax as $taxes) {
					$tax_code->taxes()->attach($taxes['tax_id'], [
						'percentage' => $taxes['percentage'],
					]);
				}
			}

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Tax Code Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Tax Code Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteTaxCode($id) {
		$delete_status = TaxCode::where('id', $id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}

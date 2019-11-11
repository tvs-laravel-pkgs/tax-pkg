<?php
namespace Abs\TaxPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class TaxPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//MASTER > TAXES
			4200 => [
				'display_order' => 10,
				'parent_id' => 2,
				'name' => 'taxes',
				'display_name' => 'Taxes',
			],
			4201 => [
				'display_order' => 1,
				'parent_id' => 4200,
				'name' => 'add-tax',
				'display_name' => 'Add',
			],
			4202 => [
				'display_order' => 2,
				'parent_id' => 4200,
				'name' => 'edit-tax',
				'display_name' => 'Edit',
			],
			4203 => [
				'display_order' => 3,
				'parent_id' => 4200,
				'name' => 'delete-tax',
				'display_name' => 'Delete',
			],

			//MASTER > TAX CODES
			4220 => [
				'display_order' => 11,
				'parent_id' => 2,
				'name' => 'tax-codes',
				'display_name' => 'Tax Codes',
			],
			4221 => [
				'display_order' => 1,
				'parent_id' => 4220,
				'name' => 'add-tax-code',
				'display_name' => 'Add',
			],
			4222 => [
				'display_order' => 2,
				'parent_id' => 4220,
				'name' => 'edit-tax-code',
				'display_name' => 'Edit',
			],
			4223 => [
				'display_order' => 3,
				'parent_id' => 4220,
				'name' => 'delete-tax-code',
				'display_name' => 'Delete',
			],

		];

		foreach ($permissions as $permission_id => $permsion) {
			$permission = Permission::firstOrNew([
				'id' => $permission_id,
			]);
			$permission->fill($permsion);
			$permission->save();
		}
		//$this->call(RoleSeeder::class);

	}
}
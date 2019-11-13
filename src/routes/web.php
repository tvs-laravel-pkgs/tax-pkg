<?php

Route::group(['namespace' => 'Abs\TaxPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'tax-pkg'], function () {
	Route::get('/taxes/get-list', 'TaxController@getTaxList')->name('getTaxList');
	Route::get('/tax/save', 'TaxController@saveTax')->name('saveTax');
	Route::get('/tax/get-form-data', 'TaxController@getFormData')->name('taxGetFormData');

	Route::get('/tax-codes/get-list', 'TaxCodeController@getTaxCodeList')->name('getTaxCodeList');
	Route::get('/tax-code/save', 'TaxCodeController@saveTaxCode')->name('saveTaxCode');
});
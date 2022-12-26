<?php

Route::group(['namespace' => 'Abs\TaxPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'tax-pkg'], function () {
	Route::get('/taxes/get-list', 'TaxController@getTaxList')->name('getTaxList');
	Route::get('/taxes/get-form-data/{id?}', 'TaxController@getTaxFormData')->name('getTaxFormData');
	Route::post('/taxes/save', 'TaxController@saveTax')->name('saveTax');
	Route::get('/taxes/delete/{id}', 'TaxController@deleteTax')->name('deleteTax');

	Route::get('/tax-codes/get-list', 'TaxCodeController@getTaxCodeList')->name('getTaxCodeList');
	Route::get('/tax-codes/get-form-data/{id?}', 'TaxCodeController@getTaxCodeFormData')->name('getTaxCodeFormData');
	Route::post('/tax-codes/save', 'TaxCodeController@saveTaxCode')->name('saveTaxCode');
	Route::get('/tax-codes/delete/{id}', 'TaxCodeController@deleteTaxCode')->name('deleteTaxCode');
	Route::get('/tax-codes/getTaxType/{id}', 'TaxCodeController@getTaxType')->name('getTaxType');
	Route::get('/tax-codes/get-tax-list', 'TaxCodeController@getTaxListInTaxCode')->name('getTaxListInTaxCode');
	Route::post('/tax-codes/get-business-data', 'TaxCodeController@getBusinessData')->name('getTaxCodeBusinessData');
});
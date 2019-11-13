<?php

Route::group(['namespace' => 'Abs\TaxPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'tax-pkg'], function () {
	Route::get('/taxes/get-list', 'TaxController@getTaxList')->name('getTaxList');
	Route::get('/taxes/get-form-data/{id?}', 'TaxController@getTaxFormData')->name('getTaxFormData');
	Route::post('/taxes/save', 'TaxController@saveTax')->name('saveTax');
	Route::get('/taxes/delete/{id?}', 'TaxController@deleteTax')->name('deleteTax');

	Route::get('/tax-codes/get-list', 'TaxCodeController@getTaxCodeList')->name('getTaxCodeList');
	Route::get('/tax-codes/get-form-data/{id?}', 'TaxCodeController@getTaxCodeFormData')->name('getTaxCodeFormData');
	Route::post('/tax-code/save', 'TaxCodeController@saveTaxCode')->name('saveTaxCode');
	Route::get('/tax-code/delete/{id?}', 'TaxCodeController@deleteTaxCode')->name('deleteTaxCode');
});
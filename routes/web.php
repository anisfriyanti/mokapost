<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


$router->post('login','UsersController@login');
$router->post('refresh/', 'UsersController@refresh');
//dropdown list menu

//generate data
$router->get('generateitemoutlet9812345764932828','GenerateController@generateitemoutlet');
$router->get('generatetransaction9812345764932828','GenerateController@generatetransaction');
$router->get('resetoutlet9812345764932828', 'GenerateController@resetstatusoutlet');
$router->get('generateoutlet9812345764932828', 'GenerateController@generateoutlet');

$router->get('company','AttendanceController@getcompany');
$router->get('fiscalyear','AttendanceController@getfiscal');
$router->get('employees', 'AttendanceController@employees');

$router->group(['prefix' => 'moka/'], function ($router) {
$router->get('outlets', 'OutletController@outlets');
// $router->get('generategross', 'GrossController@generate');
$router->get('grossprofit', 'GrossController@requestgross');
// $router->get('generateincome', 'IncomeController@generate');
$router->get('income', 'IncomeController@requestincome');
$router->get('iao', 'IaoController@requestiao');
$router->get('summaryitems', 'SummaryController@requestsummaryitems');
$router->get('summarysales', 'SummaryController@requestsummarysales');
$router->get('summary', 'SummaryController@items');
$router->get('generateitems', 'RequestController@generateitem');

$router->get('summarydashboard', 'SummaryController@summarydashboard');
});

$router->group(['prefix' => 'reports/'], function ($router) {
	$router->get('unpaidexpenseclaim','ExpenseController@requestunpaidexpense');
	$router->get('employeeloan', 'EmployeeLoanController@requestemployeeloan');
	//$router->get('test', 'ErpController@trainingresultfinal');
	$router->get('trainingresult', 'TrainingController@requesttraining');
	
	$router->get('departments','EmployeeLoanController@generatedepartment');
	$router->get('employees','EmployeeLoanController@requestemployeeresource');
	$router->get('montlyattendance','AttendanceController@getattendance');


});

// $router->group(['prefix' => 'api/'], function ($router) {


// $router->post('todo/','TodoController@store');
// $router->get('todo/', 'TodoController@index');
// $router->get('todo/{id}/', 'TodoController@show');
// $router->put('todo/{id}/', 'TodoController@update');
// $router->delete('todo/{id}/', 'TodoController@destroy');
// });

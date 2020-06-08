<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\ErpController;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
class ExpenseController extends Controller{

public function __construct(){
    $this->middleware('auth');
    date_default_timezone_set("Asia/Jakarta");
}
public function requestunpaidexpense(Request $request){

$url=$request->url();  
$page=$request->get('page');
$perPage = $request->get('limit');
$key=$request->get('search'); 
if(empty($page)){
	$page=1;
}
if(empty($perPage)){
    $perPage=10;
}

$controller= new ErpController;
$push=$controller->unpaidexpenseclaim();
$currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($push);
    
       
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $dataall= array_values($currentPageItems);
 
        $paginatedItems= new LengthAwarePaginator($dataall , count($itemCollection), $perPage);
 
      
        $paginatedItems->setPath($url);
 
         $output=$paginatedItems;
         $code=200;
     return response()->json($output,$code);  
}

}
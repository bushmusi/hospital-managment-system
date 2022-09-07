<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reception;
use App\History;
use DB;


class HistoryController extends Controller
{
    function index()
    {
    	$commonData = Reception::find($_GET['id']);
    	//$historyData = History::find($request->input('id'));
        $historyData=History::where('id',$_GET['id'])->orderBy('p_id','desc')->paginate(1);

    	$data = array( 'commonData' => $commonData,
                        'all' => $historyData,
                        'patientId' => $_GET['id'] );
    	return view('History.history')->with($data);
    }
    function printHistory()
    {

    	$commonData = Reception::find($_GET['id']);
    	//$historyData = History::find($request->input('id'));
        $historyData=History::where('id',$_GET['id'])->orderBy('p_id','desc')->paginate(1);

    	$data = array( 'commonData' => $commonData,
                        'all' => $historyData,
                        'patientId' => $_GET['id'] );
    	return view('History.printHistory')->with($data);
    }
}

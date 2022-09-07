<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reception;
use App\medInvest;
use Gate;
use DB;
use DataTables;
class PharmacyController extends Controller
{
    public function index()
    {
    	if(!Gate::allows('isPha'))
    	{
    		return redirect('/');
    	}
    	return view('pharmacy/pharmacy');
    }

    public function getdata()
    {
    	$patient = DB::table('med_invests')
    					->join('receptions','receptions.id','=','med_invests.id')
                        ->join('cashiers','cashiers.id','=','med_invests.id')
    					->select('receptions.id','receptions.fullName','receptions.opd_num','receptions.age','med_invests.medResult','med_invests.medStatus','med_invests.medName')
    					->where('med_invests.medName','!=','')
                        ->where('cashiers.med_status','!=','Unpaid')
    					->get();
    	return DataTables::of($patient)
    			->addColumn('action',function($patient){
    				if($patient->medStatus == 'Taken')
    				{
    					return '<a href="#" class="btn btn-xs btn-primary medEdit" id="'.$patient->id.'" ><i class="glyphicon glyphicon-eye-open"></i> View</a> <a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->medStatus.'</a>';
    				}
    				else
    				{
    					return '<a href="#" class="btn btn-xs btn-primary medEdit" id="'.$patient->id.'" ><i class="glyphicon glyphicon-eye-open"></i> View</a> <a href="#" class="btn btn-xs btn-success query" id="'.$patient->id.'" ><i class="glyphicon glyphicon-ok"></i> '.$patient->medStatus.'</a>';
    				}
    				
    			})
    			->make(true);
    }

    function fetchdata(Request $request)
    {
        $id = $request->input('id');
        $patient = Reception::find($id);
        $student = medInvest::find($id);
        
        $name = $patient->fullName;
        $output = array(
            'medName'    =>  $student->medName,
            'medDoz' => $student->medDoz,
            'medCat' => $student->catagory,
            'medFreq' => $student->frequency,
            'medDuration' => $student->duration
        );

        //This code is for alt list
        $medList = $output['medName'];
        $medList = explode(',', $medList);
        $new = array();
        foreach ($medList as $key => $value ) {
        	$new[$key] = "$value";
        }

        //This code is for alt result fetching 
        $medDoz = $output['medDoz'];
        $medDoz = explode(',', $medDoz);
        $resultArray = array();
        foreach ($medDoz as $key => $value) {
            $resultArray[$key] = "$value";
        }

        //This code is for med Catagory
        $medCat = $output['medCat'];
        $medCat = explode(',', $medCat);
        $medCatArray = array();
        foreach ($medCat as $key => $value ) {
            $medCatArray[$key] = "$value";
        }

        //This code is for med Duration
        $medDuration = $output['medDuration'];
        $medDuration = explode(',', $medDuration);
        $medDurationArray = array();
        foreach ($medDuration as $key => $value ) {
            $medDurationArray[$key] = "$value";
        }

        //This code is for med frequency
        $medFreq = $output['medFreq'];
        $medFreq = explode(',', $medFreq);
        $medFreqArray = array();
        foreach ($medFreq as $key => $value ) {
            $medFreqArray[$key] = "$value";
        }

        //We are sending data when he press edit btn
        $juju = array("datas" => $new,"name"=>$name,"result"=>$resultArray,'medCat'=>$medCatArray,'medDuration'=>$medDurationArray,'medFreq'=>$medFreqArray);
        echo json_encode($juju);
    }
    function saveAction(Request $request)
    {
        $student = medInvest::find($request->input('id'));
        $student->medStatus ='Taken';
        $student->save();
        $output = array(
            'success'   =>  "Reported as medicine has tooken by patient !!!"
        );
        echo json_encode($output);
            
    }
}

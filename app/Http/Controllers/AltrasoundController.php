<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reception;
use App\altInvest;
use DB;
use DataTables;
use Validator;

class AltrasoundController extends Controller
{
    public function index()
    {
    	return view('altrasound/altrasound');
    }

    public function getdata()
    {
    	$patient = DB::table('alt_invests')
                        ->join('receptions', 'alt_invests.id', '=', 'receptions.id')
                        ->join('cashiers', 'cashiers.id', '=', 'receptions.id')
                        ->select('alt_invests.id', 'receptions.fullName','receptions.opd_num','receptions.age','alt_invests.altStatus','alt_invests.altResult')
                        ->where('alt_invests.altName','!=','')
                        ->where('cashiers.alt_status','!=','Unpaid')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    if($patient->altStatus == 'Sent')
                    {
                        return '<a href="#" class="btn btn-xs btn-primary " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-edit"></i> Insert</a> <a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->altStatus.'</a>';
                    }
                    else
                    {
                    	if($patient->altResult == '')
                    	{
                    		return '<a href="#" class="btn btn-xs btn-primary altEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Insert</a> <a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->altStatus.'</a>';
                    	}
                    	else
                    	{
                    		return '<a href="#" class="btn btn-xs btn-primary altEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Insert</a> <a href="#" class="btn btn-xs btn-success query" id="'.$patient->id.'"><i class="glyphicon glyphicon-ok"></i> '.$patient->altStatus.'</a>';
                    	}
                        
                    }
                    
                })
                ->make(true);
    }
    function fetchdata(Request $request)
    {
        $id = $request->input('id');
        $patient = Reception::find($id);
        $student = altInvest::find($id);
        
        $name = $patient->fullName;
        $output = array(
            'altName'    =>  $student->altName,
            'altResult' => $student->altResult,
        );


        //New methodology
        $catList = $student->catagory;
        $catList = explode(',', $catList);

        //
        $temp1 = array();
        $temp2 = $student->altName;
        $temp2 = explode('&', $temp2);
        $temp3 = '';
        foreach ($temp2 as $key => $value) {
            if($key == 0)
            {
                $temp3 .= $value;
            }
            else
            {
                $temp3 .=','.$value;
            }
        }
        $temp3 = explode(',', $temp3);
        $temp4 = array();

        $altResultList = $student->altResult;
        $altResultList = explode(',', $altResultList);
        foreach ($temp3 as $key => $value) {
            $test = $temp3[$key];
            $altDB =DB::table('alt_finances')
                            ->select('altName','catagory','_option','optionList')
                            ->where('altName',$test)
                            ->get();                         
            $temp5 = $temp3[$key];
            $temp5 .= '♀'.$altDB[0]->catagory;
            $temp5 .= '♀'.$altDB[0]->_option;
            $temp5 .= '♀'.$altDB[0]->optionList;
            if(count($altResultList) > 1)
            {
                $temp5 .= '♀'.$altResultList[$key];
            }
            else
            {
                $temp5 .= '♀'.'';
            }
            
            $temp4[$key] = $temp5;                         
        }

        //This code is for lab list
        $altList = $output['altName'];
        $altList = explode(',', $altList);
        $new = array();
        foreach ($altList as $key => $value ) {
            $new[$key] = "$value";
        }

        //This code is for lab result fetching 
        $altResult = $output['altResult'];
        $altResult = explode(',', $altResult);
        $resultArray = array();
        foreach ($altResult as $key => $value) {
            $resultArray[$key] = "$value";
        }

        //We are sending data when he press edit btn
        $juju = array("datas" => $new,"name"=>$name,"result"=>$resultArray,"altDetail"=>$temp4,'catList'=>$catList);
        echo json_encode($juju);
    }

    function postdata(Request $request)
    {
        $resultLength = $request->get('button_action');
        $altResult = array();
        $variable = array();
        for($x=0;$x<$resultLength;$x++ ) {

            $altResult[$x] = $request->get('cmn'.$x.'');
            $altResult[$x] .=' & '.$request->get('ozr'.$x.'');
            $variable['cmn'.$x.''] = 'required';

        }
       
        $validation = Validator::make($request->all(), $variable);
        $error_array = array();
        $success_output = '';
        if ($validation->fails())
        {
            foreach ($validation->messages()->getMessages() as $field_name => $messages)
            {
                $error_array[] = $messages; 
            }
        }
        else
        {
            //I am fetching Cashier total_fee using his id
            $student = altInvest::find($request->get('student_id'));
            //here is the main code for updating value
            $student->altResult = '';
            $student->save();
            for($x=0;$x<count($altResult);$x++)
            {
                $student->altResult .= ''.$altResult[$x].'';
                if($x == count($altResult)-1)
                {
                    $student->altResult .='';
                }
                else
                {
                    $student->altResult .=',';
                }
            }
            $student->save();
            $success_output = '<div class="alert alert-success">Altrasound Result Saved Successfully</div>';
            
        }
        
        $output = array(
            'error'     =>  $validation->errors(),
            'success'   =>  $success_output
        );
        echo json_encode($output);
    }
    function saveAction(Request $request)
    {
        $student = altInvest::find($request->input('id'));
        $student->altStatus ='Sent';
        $student->save();
        $output = array(
            'success'   =>  "Transferred to opd room Successfully"
        );
        echo json_encode($output);
            
    }
}

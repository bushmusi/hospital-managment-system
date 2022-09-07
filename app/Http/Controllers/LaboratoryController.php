<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reception;
use App\labInvest;
use Gate;
use DB;
use DataTables;
use Validator;

class LaboratoryController extends Controller
{
    public function index()
    {
    	//Secure the function
    	if(!Gate::allows('isLab'))
        {
            return redirect('/');
        }
    	return view('laboratory/lab');
    }

    public function getdata()
    {
    	$patient = DB::table('lab_invests')
                        ->join('receptions', 'lab_invests.id', '=', 'receptions.id')
                        ->join('cashiers','cashiers.id','=','lab_invests.id')
                        ->select('lab_invests.id', 'receptions.fullName','receptions.opd_num','receptions.age','lab_invests.labStatus','lab_invests.labResult')
                        ->where('cashiers.lab_status','=','Paid')
                        ->where('lab_invests.labName','!=','')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    if($patient->labStatus == 'Sent')
                    {
                        return '<a href="#" class="btn btn-xs btn-primary " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-edit"></i> Insert</a> <a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->labStatus.'</a>';
                    }
                    else
                    {
                        if($patient->labResult == '')
                        {
                            return '<a href="#" class="btn btn-xs btn-primary labEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Insert</a> <a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->labStatus.'</a>';
                        }
                        else
                        {
                            return '<a href="#" class="btn btn-xs btn-primary labEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Insert</a> <a href="#" class="btn btn-xs btn-success query" id="'.$patient->id.'"><i class="glyphicon glyphicon-ok"></i> '.$patient->labStatus.'</a>';
                        }
                        
                    }
                    
                })
                ->make(true);
    }

    function fetchdata(Request $request)
    {
        $id = $request->input('id');
        $patient = Reception::find($id);
        $student = labInvest::find($id);
        
        $name = $patient->fullName;
        $output = array(
            'labName'    =>  $student->labName,
            'labResult' => $student->labResult,
        );


        //New methodology
        $catList = $student->catagory;
        $catList = explode(',', $catList);

        //
        $temp1 = array();
        $temp2 = $student->labName;
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

        $labResultList = $student->labResult;
        $labResultList = explode(',', $labResultList);
        foreach ($temp3 as $key => $value) {
            $test = $temp3[$key];
            $labDB =DB::table('lab_finances')
                            ->select('labName','catagory','_option','optionList')
                            ->where('labName',$test)
                            ->get();                         
            $temp5 = $temp3[$key];
            $temp5 .= '♀'.$labDB[0]->catagory;
            $temp5 .= '♀'.$labDB[0]->_option;
            $temp5 .= '♀'.$labDB[0]->optionList;
            if(count($labResultList) > 1)
            {
                $temp5 .= '♀'.$labResultList[$key];
            }
            else
            {
                $temp5 .= '♀'.'';
            }
            $temp4[$key] = $temp5;                         
        }

        //This code is for lab list
        $labList = $output['labName'];
        $labList = explode(',', $labList);
        $new = array();
        foreach ($labList as $key => $value ) {
        	$new[$key] = "$value";
        }

        //This code is for lab result fetching 
        $labResult = $output['labResult'];
        $labResult = explode(',', $labResult);
        $resultArray = array();
        foreach ($labResult as $key => $value) {
            $resultArray[$key] = "$value";
        }

        //We are sending data when he press edit btn
        $juju = array("datas" => $new,"name"=>$name,"result"=>$resultArray,"labDetail"=>$temp4,'catList'=>$catList
    );
        echo json_encode($juju);
    }
    
    function postdata(Request $request)
    {
        $resultLength = $request->get('button_action');
        $labResult = array();
        $variable = array();
        for($x=0;$x<$resultLength;$x++ ) {

            $labResult[$x] = $request->get('cmn'.$x.'');
            $labResult[$x] .=' & '.$request->get('ozr'.$x.'');
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
            $student = labInvest::find($request->get('student_id'));
            //here is the main code for updating value
            $student->labResult = '';
            $student->save();
            for($x=0;$x<count($labResult);$x++)
            {
                $student->labResult .= ''.$labResult[$x].'';
                if($x == count($labResult)-1)
                {
                    $student->labResult .='';
                }
                else
                {
                    $student->labResult .=',';
                }
            }
            $student->save();
            $success_output = '<div class="alert alert-success">Laboratory Result Saved Successfully</div>';
            
        }
        
        $output = array(
            'error'     =>  $validation->errors(),
            'success'   =>  $success_output
        );
        echo json_encode($output);
    }


    function saveAction(Request $request)
    {
        $student = labInvest::find($request->input('id'));
        $student->labStatus ='Sent';
        $student->save();
        $output = array(
            'success'   =>  "Transferred to opd room Successfully"
        );
        echo json_encode($output);
            
    }
}

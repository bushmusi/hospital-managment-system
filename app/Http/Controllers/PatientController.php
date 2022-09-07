<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Reception;
use App\history;
use App\Cashier;
use App\medInvest;
use App\opd1;
use App\opd2;
use App\opd3;
use App\opd4;

use DataTables;
use Gate;
use DB;
use Carbon\Carbon;

class PatientController extends Controller
{
    function index()
    {
        if(!Gate::allows('isReception'))
        {
            return redirect('/');
        }
        $reagion = DB::table('state_cities')
                        ->groupBy('state')
                        ->get();
     return view('reception.prevPatient')->with('reagion',$reagion);
     //http://127.0.0:8000/ajaxdata
    }

    function cityFetch(Request $request)
    {
        // $value = $request->get('reagion');
        // echo "hello";
        $selectedReagion = $request->get('reagionValue');
        $reagionCity = DB::table('state_cities')
                        ->where('state',$selectedReagion)
                        ->groupBy('city')
                        ->get();

        $option = "<option value=''>------------------------------------------Select Subcity--------------------------------------</option>";
        foreach ($reagionCity as $key => $value) 
        {
            $option.="<option value='".$value->city."'>".$value->city."</option>";
        }                    
        $output  = array('well' => $reagionCity[0]->city ,'option'=>$option);
        echo json_encode($output);
    }

    function getdata()
    {
         $patient = Reception::select('id', 'fullName','type', 'phone','gender', 'opd_num','status','updated_at');
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    if($patient->status != 'Registered')
                    {
                        return '<a href="#" class="btn btn-xs btn-primary edit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Edit</a> <a href="#" class="btn btn-xs btn-success delete" id="'.$patient->id.'"><i class="glyphicon glyphicon-ok"></i> '.$patient->status.'</a>';
                    }
                    else
                    {
                        return '<a href="#" class="btn btn-xs btn-primary edit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Edit</a> <a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->status.'</a>';
                    }
                })
                ->make(true);
    }

    function postdata(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'fullName' => 'required|string',
            'type' => 'required',
            'phone' => 'required',
            'opd_num' => 'required',
            'age' => 'required',
            'reagion' => 'required',
            'subcity' => 'required',
            'gender' => 'required',
        ]);
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
            if($request->get('button_action') == 'insert')
            {
                $student = new Reception([
                    'fullName'    =>  $request->get('fullName'),
                    'type'    =>  $request->get('type'),
                    'phone'    =>  $request->get('phone'),
                    'age'    =>  $request->get('age'),
                    'opd_num'    =>  $request->get('opd_num'),
                    'gender'    =>  $request->get('gender'),
                    'reagion'    =>  $request->get('reagion'),
                    'subcity'    =>  $request->get('subcity'),
                ]);
                $student->save();
                $success_output = '<div class="alert alert-success">'.$request->get('fullName').' registered successfully</div>';
            }

            if($request->get('button_action') == 'update')
            {
                $recepData = Reception::find($request->get('student_id'));
                if($recepData->opd_num == $request->get('opd_num'))
                {
                    $student=DB::table('receptions')->where('id',$request->get('student_id'))->update(
                        [
                          'fullName'=>$request->get('fullName'),
                          'type'=>$request->get('type'),
                          'phone'=>$request->get('phone'),
                          'opd_num'=>$request->get('opd_num'),
                          'age'=>$request->get('age'),
                          'gender'=>$request->get('gender'),
                          'reagion'=>$request->get('reagion'),
                          'subcity'=>$request->get('subcity') 
                        ]
                    );
                }
                else
                {
                    $student=DB::table('receptions')->where('id',$request->get('student_id'))->update(
                        [
                          'fullName'=>$request->get('fullName'),
                          'type'=>$request->get('type'),
                          'phone'=>$request->get('phone'),
                          'opd_num'=>$request->get('opd_num'),
                          'age'=>$request->get('age'),
                          'gender'=>$request->get('gender'),
                          'reagion'=>$request->get('reagion'),
                          'subcity'=>$request->get('subcity') 
                        ]
                    );
                    
                    $tabName=strtolower($request->get('opd_num')).'s';
                    DB::table($tabName)
                        ->insert(['id'=>$request->get('student_id'),'created_at'=>$recepData->freshTimestamp(),'updated_at'=>$recepData->freshTimestamp()]);
                    DB::table($recepData->opd_num.'s')
                        ->where('id',$request->get('student_id'))
                        ->delete();
                }
                $success_output = '<div class="alert alert-success">'.$request->get('fullName').' data updated successfully!!!</div>';
            }
            
        }
        
        $output = array(
            'error'     =>  $error_array,
            'success'   =>  $success_output
        );
        echo json_encode($output);
    }



    function fetchdata(Request $request)
    {
        $id = $request->input('id');
        $student = Reception::find($id);
        $subcity = DB::table('state_cities')
                        ->where('state',$student->reagion)
                        ->groupBy('city')
                        ->get();
        $option = "<option>-----------------------------------------Select Subcity--------------------------------------------</option>";
        foreach ($subcity as $key => $value) {
            $option .= "<option value='".$value->city."'>".$value->city."</option>";
        }


        $output = array(
            'fullName'    =>  $student->fullName,
            'phone'    =>  $student->phone,
            'type'    =>  $student->type,
            'gender'    =>  $student->gender,
            'age'    =>  $student->age,
            'opd_num'    =>  $student->opd_num,
            'reagion'    =>  $student->reagion,
            'subcity'    =>  $student->subcity,
            'subcityList' => $option,
        );
        echo json_encode($output);
    }

    function removedata(Request $request)
    {
        $student = Reception::find($request->input('id'));
        $userId = $request->input('id');
        $student->status ='Registered';
        $lastCome= $student->updated_at;
        $dateCounter = substr($student->updated_at,0,10);
        $dateDifference = Carbon::parse(Carbon::now())->diffInDays($dateCounter);
        $human = $student->updated_at->diffForHumans();


        //After calculating date
        $card = 0;
        if($dateDifference > 10)
        {
            $card = 10;
        }
        
         //Let we calculate the fee for card
        $receptionData = Reception::find($userId);
        $patientTypeData = DB::table('patient_types')
                                ->select('fee')
                                ->where('name','=',$receptionData->type)
                                ->get();
        //Now let we calculate data
        foreach ($patientTypeData as  $value) {
            $rate= $value->fee;
        }
        $total = $card;
        $discount = $rate * $card;
        $net = $total - $discount;
        //Let we check if patient name is in pharmacy and cashier first
        $cashierData = Cashier::find($request->input('id'));
        $findMedcine = medInvest::find($request->input('id'));
        if($cashierData)
            $cashierData->delete();
        if($findMedcine)
            $findMedcine->delete();


        $cashier =DB::table('cashiers')->insert(
                ['id' => $userId,
                'lastSeen' =>$lastCome,
                'dateBefore' => $student->updated_at->diffForHumans(),
                'total_fee' => $total,
                'discount_fee' => $discount,
                'net_fee' => $net,
                'created_at' => $student->freshTimestamp()]
                
                );
        
        $output = array(
            'success' => $receptionData->fullName.' successfully Transfered To Finance Room',
            'error' =>   'It already registered',
             'human' => $human,
        );
        if($student->save())
        {
            echo json_encode($output);
        }
    }
} 

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use App\labFinance;
use App\altFinance;
use App\medFinance;
use Validator;
use DB;

class ManagementController extends Controller
{
    //
     public function labFetch()
    {
        $patient = DB::table('lab_finances')
                        ->select('id','labName','price','catagory','_option','optionList','updated_at')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    return '<a href="#" class="btn btn-xs btn-primary edit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Edit</a>  <a href="#" class="btn btn-xs btn-danger delete" id="'.$patient->id.'"><i class="fa fa-remove"></i> Delete</a> ';
                })
                ->make(true);
    }

    public function altFetch()
    {
        $patient = DB::table('alt_finances')
                        ->select('id','altName','price','catagory','_option','optionList','updated_at')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    return '<a href="#" class="btn btn-xs btn-primary altEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Edit</a>  <a href="#" class="btn btn-xs btn-danger altDelete" id="'.$patient->id.'"><i class="fa fa-remove"></i> Delete</a> ';
                })
                ->make(true);
    }

    public function medFetch()
    {
        $patient = DB::table('med_finances')
                        ->select('id','medName','catagory','price','quantity','updated_at')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    return '<a href="#" class="btn btn-xs btn-primary medEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Edit</a>  <a href="#" class="btn btn-xs btn-danger medDelete" id="'.$patient->id.'"><i class="fa fa-remove"></i> Delete</a> ';
                })
                ->make(true);
    }

    public function destroy(Request $request)
    {
        $labRemoved = DB::table('lab_finances')
                        ->where('id',$request->input('id'))
                        ->delete();
        if($labRemoved)
        {
            $output = array('success' => 'Successfully Removed!!!' );
        }
        else
        {
            $output = array('success' => 'Not deleted');
        }
        echo json_encode($output);
    }

    public function labNewAdd(Request $request)
    {

        if($request->get('labBtnAction') == 'Update')
        {
            
            if($request->get('prevLabValue') == $request->get('labName'))
            {
                $validationList = array(
                                            'labPrice' => 'required|numeric|min:0',
                                            'labName' => 'required|string',
                                            'labCatagory' => 'required|string',
                                            'labResultOption' => 'required|string'
                                        );
            }
            else
            {
                $validationList = array(
                                            'labPrice' => 'required|numeric|min:0',
                                            'labName' => 'required|string|unique:lab_finances,labName',
                                            'labCatagory' => 'required|string',
                                            'labResultOption' => 'required|string'
                                        );
            }
            if($request->get('optionCountID') != '')
            {
                $count=$request->get('optionCountID');
                for ($i=1; $i <= $count; $i++) { 
                    $validationList['option_'.$i] = 'required|string';
                }
            }
            $validation = Validator::make($request->all(),$validationList);


            if($validation->passes())
            {
                $labName = labFinance::find($request->get('labHuluBtnId'));
                $labName->labName = $request->input('labName');
                $labName->price = $request->input('labPrice');
                $labName->catagory = $request->get('labCatagory');
                $labName->_option = $request->get('labResultOption');
                
                $optionList='';
                if($request->get('optionCountID') != '')
                {
                    $count=$request->get('optionCountID');
                    for ($i=1; $i <= $count; $i++) { 
                        if( $i == $count)
                        {
                            $optionList .= $request->get('option_'.$i);
                        }
                        else
                        {
                            $optionList .= $request->get('option_'.$i).',';
                        }
                        
                    }
                }
                $labName->optionList = $optionList;
                $labName->save();
                $success = '<div class="alert alert-success">Successfully updated !!! </div>';
                echo json_encode(['success' => $success]);
            }   
            else
            {
                echo json_encode(["errors" => $validation->errors() ]);
            }
        }  
        else
        {
            $validationList = array(

                                        'labName' => 'required|string|unique:lab_finances,labName',
                                        'labPrice' => 'required|numeric|min:0',
                                        'labCatagory' => 'required|string',
                                        'labResultOption' => 'required|string'
                                    );
            if($request->get('optionCountID') != '')
            {
                $count=$request->get('optionCountID');
                for ($i=1; $i <= $count; $i++) { 
                    $validationList['option_'.$i] = 'required|string';
                }
            }
            $validation = Validator::make($request->all(),$validationList);


            if($validation->passes())
            {
                $labName = new labFinance;
                    $labName->labName = $request->input('labName');
                    $labName->price = $request->input('labPrice');
                    $labName->catagory = $request->get('labCatagory');
                    $labName->_option = $request->get('labResultOption');
                    
                    $optionList='';
                    if($request->get('optionCountID') != '')
                    {
                        $count=$request->get('optionCountID');
                        for ($i=1; $i <= $count; $i++) { 
                            if( $i == $count)
                            {
                                $optionList .= $request->get('option_'.$i);
                            }
                            else
                            {
                                $optionList .= $request->get('option_'.$i).',';
                            }
                            
                        }
                    }
                    $labName->optionList = $optionList;
                    $labName->save();
                    $success = '<div class="alert alert-success">Successfully Added !!! </div>';
                    echo json_encode(["success"=>$success]);
            }
            else
            {
                echo json_encode(["errors" => $validation->errors() ,'dude'=>$request->get('labBtnAction') ]);
            }
        } 
        
    }

    public function labEdit(Request $request)
    {
        $labData = labFinance::find($request->input('id'));
        $labName = array('labName' => $labData->labName,'price' => $labData->price ,'id' => $request->input('id'), 'catagory' => $labData->catagory , 'option' => $labData->_option, 'optionList' => $labData->optionList);
        echo json_encode($labName);
    }

    public function labUpdate(Request $request)
    {
        if($request->get('prevLabValue') == $request->get('labEditName'))
        {
            $validation = Validator::make($request->all(), [
                'labEditName' => 'required|string',
                'labPriceEdit' => 'required|numeric|min:0',
            ]);
        }
        else
        {
            $validation = Validator::make($request->all(), [
                'labEditName' => 'required|string|unique:lab_finances,labName',
                'labPriceEdit' => 'required|numeric|min:0',
            ]);
        }
        
        $error_array = array( );
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
            $labUpdateTask = labFinance::find($request->get('lab_id'));
            $labUpdateTask->labName = $request->get('labEditName');
            $labUpdateTask->price = $request->get('labPriceEdit');
            $labUpdateTask->save();
            $success_output = '<div class="alert alert-success">Successfully updated !!! </div>';
        }
        $output = array('error' => $error_array ,'success'=>$success_output );
        echo json_encode($output);
    }
    public function newAdd(Request $request)
    {
        if($request->get('newSubmitType') == 'altNewSubmit')
        {
            $validationList = array(
                    'altrasoundName' => 'required|string|unique:alt_finances,altName',
                    'altrasoundPrice' => 'required|numeric|min:0',
                    'altCatagory' => 'required',
                    'altResultOption' => 'required'
            );
            if($request->get('alt_optionCountID') != '')
            {
                $count=$request->get('alt_optionCountID');
                for ($i=1; $i <= $count; $i++) { 
                    $validationList['alt_option_'.$i] = 'required|string';
                }
            }
            $validation = Validator::make($request->all(),$validationList);


            $error_array = array();
            $success = '';
            if($validation->fails())
            {
                foreach ($validation->messages()->getMessages() as $field_name => $messages) {
                    # code...
                    $error_array[] = $messages;
                }
                $output = array('errors'=> $validation->errors());
            }
            else
            {
                $altAdder = new altFinance;
                $altAdder->altName = $request->get('altrasoundName');
                $altAdder->price = $request->get('altrasoundPrice');
                $altAdder->catagory = $request->get('altCatagory');
                $altAdder->_option = $request->get('altResultOption');
                if($request->get('alt_optionCountID') != '')
                {
                    $count=$request->get('alt_optionCountID');
                    $optionList = '';
                    for ($i=1; $i <= $count; $i++) 
                    { 
                        if($i == $count)
                        {
                            $optionList .= $request->get('alt_option_'.$i);
                        }
                        else
                        {
                            $optionList .= $request->get('alt_option_'.$i).',';    
                        }
                        
                    }
                    $altAdder->optionList = $optionList;
                }
                $altAdder->save();
                $success = '<div class="alert alert-success">Successfully added !!! </div>';
                $output = array('success' =>$success);
            }
            
            echo json_encode($output);
        }
        elseif ($request->get('newSubmitType') == 'altUpdateSubmit') 
        { 
            

            if($request->get('prevAltValue') == $request->get('altrasoundName'))
            {
                $validationList = array(
                    'altrasoundName' => 'required|string',
                    'altrasoundPrice' => 'required|numeric|min:0',
                    'altCatagory' => 'required',
                    'altResultOption' => 'required');
            }
            else
            {
                $validationList = array(
                'altrasoundName' => 'required|string|unique:alt_finances,altName',
                'altrasoundPrice' => 'required|numeric|min:0',
                'altCatagory' => 'required',
                'altResultOption' => 'required');
            }




            if($request->get('alt_optionCountID') != '')
            {
                $count=$request->get('alt_optionCountID');
                for ($i=1; $i <= $count; $i++) { 
                    $validationList['alt_option_'.$i] = 'required|string';
                }
            }
            $validation = Validator::make($request->all(),$validationList);

            $error_array = array();
            $success = '';
            if($validation->fails())
            {
                foreach ($validation->messages()->getMessages() as $key => $value) {
                    $error_array[] = $value;
                }
                $output = array('errors'=>$validation->errors() );
            }
            else
            {
                $altUpdateTask = altFinance::find($request->get('updatedItemID'));
                $altUpdateTask->altName = $request->get('altrasoundName');
                $altUpdateTask->price = $request->get('altrasoundPrice');
                $altUpdateTask->catagory = $request->get('altCatagory');
                $altUpdateTask->_option = $request->get('altResultOption');
                if($request->get('alt_optionCountID') != '')
                {
                    $count=$request->get('alt_optionCountID');
                    $optionList = '';
                    for ($i=1; $i <= $count; $i++) 
                    { 
                        if($i == $count)
                        {
                            $optionList .= $request->get('alt_option_'.$i);
                        }
                        else
                        {
                            $optionList .= $request->get('alt_option_'.$i).',';    
                        }
                        
                    }
                    $altUpdateTask->optionList = $optionList;
                }
                $altUpdateTask->save();
                $success = '<div class="alert alert-success">Successfully updated !!! </div>';
                $output = array('success' => $success );
            }
            
            echo json_encode($output);

        }
        else
        {
           echo json_encode('hello world');
        }
        
    }

    public function altEdit(Request $request)
    {
        $altUpdate = altFinance::find($request->get('id'));
        $output = array('altrasoundName' => $altUpdate->altName,'altrasoundPrice' => $altUpdate->price,'altID'=>$altUpdate->id ,'altCat'=>$altUpdate->catagory,'_option'=>$altUpdate->_option,'optionList'=>$altUpdate->optionList);
        echo json_encode($output);
    }

    public function altDestroy(Request $request)
    {

        $altDataDestroy = altFinance::find($request->get('id'));
        if($altDataDestroy != '' )
        {
            $altDataDestroy->delete();
            $output = array('success' => "Successfully Removed!!!" );
        }
        else
        {
            $output = array('success' => " Not Removed!!!" );
        }
        echo json_encode($output);
    }

    public function medEdit(Request $request){
        $medUpdate = medFinance::find($request->get('id'));
        $output = array('medicineName' => $medUpdate->medName ,'medicinePrice'=>$medUpdate->price,'id'=>$request->get('id') , 'medicineCatagory'=>$medUpdate->catagory,'medicineQuantity'=>$medUpdate->quantity);
        echo json_encode($output);
    }
    public function medNewAdd(Request $request)
    {
        
        $success='';
        $output;
        if($request->get('medNewSubmitType') == 'medNewSubmit')
        {
            $validation = Validator::make($request->all(),[
                'medicineName' => 'required|string|unique:med_finances,medName',
                'medicineQuantity' => 'required|numeric|min:1',
                'medicinePrice' => 'required|numeric|min:0',
                'medicineCatagory' => 'required|string'
            ]);
            if($validation->fails())
            {
               $output = array('error'=>$validation->errors());
            }
            else
            {
                $newMed = new medFinance;
                $newMed->medName = $request->get('medicineName');
                $newMed->price = $request->get('medicinePrice');
                $newMed->catagory = $request->get('medicineCatagory');
                $newMed->quantity = $request->get('medicineQuantity');
                $newMed->save();

                $medCatogoryList = DB::table('med_finances')
                                    ->select('catagory')
                                    ->groupBy('catagory')
                                    ->get();
                $arrayCatList = '';
                foreach ($medCatogoryList as $key => $value) 
                {
                    if($key==0)
                    {
                        $arrayCatList .= ''.$value->catagory.'';
                    }
                    else
                    {
                        $arrayCatList .= ','.$value->catagory.'';
                    }
                }
                $arrayCatList = explode(',', $arrayCatList);

                $output = array('success' =>'<div class="alert alert-success">Successfully added !!! </div>' , 'medCatList'=>$arrayCatList);
                    
            }
        }
        elseif($request->get('medNewSubmitType') == 'medUpdateSubmit')
        {
            
            if($request->get('prevMedValue') == $request->get('medicineName'))
            {
                $validation = Validator::make($request->all(),[
                    'medicineName' => 'required|string',
                    'medicineQuantity' => 'required|numeric|min:1',
                    'medicinePrice' => 'required|numeric|min:0',
                    'medicineCatagory' => 'required|string'
                ]);
            }
            else
            {
                $validation = Validator::make($request->all(),[
                    'medicineName' => 'required|string|unique:med_finances,medName',
                    'medicineQuantity' => 'required|numeric|min:1',
                    'medicinePrice' => 'required|numeric|min:0',
                    'medicineCatagory' => 'required|string'
                ]);
            }





            if($validation->fails())
            {
                $output = array('error' => $validation->errors());
            }
            else
            {
                $updatemedicineName = medFinance::find($request->get('medUpdatedItemID'));
                if($updatemedicineName != '')
                {
                    $updatemedicineName->medName = $request->get('medicineName');
                    $updatemedicineName->price = $request->get('medicinePrice');
                    $updatemedicineName->catagory = $request->get('medicineCatagory');
                    $updatemedicineName->quantity = $request->get('medicineQuantity');
                    $updatemedicineName->save();

                    $medCatogoryList = DB::table('med_finances')
                                        ->select('catagory')
                                        ->groupBy('catagory')
                                        ->get();
                    $arrayCatList = '';
                    foreach ($medCatogoryList as $key => $value) 
                    {
                        if($key==0)
                        {
                            $arrayCatList .= ''.$value->catagory.'';
                        }
                        else
                        {
                            $arrayCatList .= ','.$value->catagory.'';
                        }
                    }
                    $arrayCatList = explode(',', $arrayCatList);
                    
                    $output = '<div class="alert alert-success">Successfully updated !!! </div>'; 
                    $output = array('success' =>'<div class="alert alert-success">Successfully updated !!! </div>' , 'medCatList'=>$arrayCatList);
                }
                else
                {
                    $output = '<div class="alert alert-danger">Sorry ... we can not find record !!! </div>';
                }
               
                
            }
            
        }
        echo json_encode($output);
    }


    public function medRemove(Request $request)
    {
        $dataToRemove = medFinance::find($request->get('id'));
        $error='';
        $success='';
        if($dataToRemove != '')
        {
            $dataToRemove->delete();
            $success = 'Successfully Removed!!!';
        }
        else
        {
            $error = 'Sorry....We can not find an item';
        }
        $output = array('success' => $success,'error'=>$error );
        echo json_encode($output);
    }
}

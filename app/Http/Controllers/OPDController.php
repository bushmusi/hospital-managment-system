<?php

namespace App\Http\Controllers;
use Validator;
use Illuminate\Http\Request;
use App\opd1;
use App\opd2;
use App\opd3;
use App\opd4;
use App\Reception;
use App\labInvest;
use App\labFinance;
use App\altInvest;
use App\altFinance;
use App\medInvest;
use App\medFinance;
use App\history;
use App\Cashier;
use Gate;
use DB;
use DataTables;
use Auth;
use Carbon\Carbon;

class OPDController extends Controller
{
    //Here is index method that calls for opd
    private $role;
    
    public function index()
    {
        if(!Gate::allows('isOpd1') && !Gate::allows('isOpd2') && !Gate::allows('isOpd3') && !Gate::allows('isOpd4'))
        {
            return redirect('/');
        }
        $this->role = Auth::user()->role;
        $labList = labFinance::all();
        $labCatagory = DB::table('lab_finances')
                            ->groupBy('catagory')
                            ->get();   
        $altCatagory = DB::table('alt_finances')
                            ->groupBy('catagory')
                            ->get();                          
        $altList = altFinance::all();
        $medList = medFinance::all();
        //Here we will allocate to the view which redirect  to
        return view('opd/opd')->with(['labList'=>$labList,'altList'=>$altList,'medList'=>$medList,'catagory'=>$labCatagory,'alt_catagory'=>$altCatagory]);
    }



   

    //This method is to fetch all data patient directly transferred to opd room class
    public function getdata()
    {
         //$patient = Cashier::select('id', 'total_fee','discount_fee', 'net_fee','status');
        $tableName =  Auth::user()->role;
        $tableName =$tableName.'s';
        
         $patient = DB::table(''.$tableName.'')
                        ->join('receptions', ''.$tableName.'.id', '=', 'receptions.id')
                        ->select(''.$tableName.'.id', 'receptions.fullName', 'receptions.age', 'receptions.type',''.$tableName.'.hx',''.$tableName.'.status',''.$tableName.'.created_at')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    return '<table ><thead > <tr ><th><a href="#" class="btn btn-xs btn-primary edit" id="'.$patient->id.'"><i class="glyphicon glyphicon-edit"></i> Diagnosis</a></th>  <th><a href="#" class="btn btn-xs btn-success altrasound" id="'.$patient->id.'"><i class="glyphicon glyphicon-cloud"></i> Ultrasound</a><th></tr> <tr><th><a href="#" class="btn btn-xs btn-primary medEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-grain"></i> Medicine</a></th> <th><a class="btn btn-xs btn-success investigation" id="'.$patient->id.'" target="_blank"><i class="glyphicon glyphicon-trash"></i> Laboratory</a></th> </tr> <tr> <th><a href="../history?id='.$patient->id.'&page=1" class="btn btn-xs btn-primary history" id="'.$patient->id.'" target="_blank"><i class="glyphicon glyphicon-book"></i> History</a></th><th><a href="#" class="btn btn-xs btn-success result" id="'.$patient->id.'"><i class="glyphicon glyphicon-ok"></i> Result</a></th> </tr></thead></table>  ';
                })
                ->make(true);
    }



    //This method is used for fetching older data to patient Reception class form modal
    function fetchdata(Request $request)
    {
        //We are setting role to tableName //Let we set an array of opd rooms in opdarray
        

        $id = $request->input('id');
        $student = Reception::find($id);


        //Here we set aloop for getting the name of opd number
        $tableName =  Auth::user()->role;
        $tableName =ucfirst($tableName);
        $opdArray = array('Opd1','Opd2','Opd3','Opd4');
        $x = 0;
        while($opdArray[$x] != $tableName)
        {
            $x++;
        }
        if($x == 0)
        {
            $patient = Opd1::find($id);
        }
        else if($x == 1)
        {
            $patient = Opd2::find($id);
        }
        else if($x == 2)
        {
            $patient = Opd3::find($id);
        }
        else if($x == 3)
        {
            $patient = Opd4::find($id);
        }
        
        $output = array(
            'fullName'    =>  $student->fullName,
            'hx'    =>  $patient->hx,
            'dx'    =>  $patient->dx,
        );
        echo json_encode($output);   
    } 


    // //This method is fetches the data of  result
    function result_fetchdata(Request $request)
    {
        $id = $request->input('id');
        $student = Reception::find($id);
        $labInvest = labInvest::find($id);
        $cashierData = Cashier::find($id);
        if($labInvest->labStatus != 'Sent')
        {
            $labInvest->labResult = null;
        }
        $altInvest = altInvest::find($id);
        if($altInvest->altStatus != 'Sent')
        {
            $altInvest->altResult = null;
        }
        $medInvest = medInvest::find($id);

        //Here we set aloop for getting the name of opd number
        $tableName =  Auth::user()->role;
        $tableName =ucfirst($tableName);
        $opdArray = array('Opd1','Opd2','Opd3','Opd4');
        $x = 0;
        while($opdArray[$x] != $tableName)
        {
            $x++;
        }
        if($x == 0)
        {
            $patient = Opd1::find($id);
        }
        else if($x == 1)
        {
            $patient = Opd2::find($id);
        }
        else if($x == 2)
        {
            $patient = Opd3::find($id);
        }
        else if($x == 3)
        {
            $patient = Opd4::find($id);
        }


        $output = array(
            'fullName'    =>  $student->fullName,
            'result'    =>  $patient->result,
            'labName'    =>  $labInvest->labName,
            'labResult'    =>  $labInvest->labResult,
            'altName'    =>  $altInvest->altName,
            'altResult'    =>  $altInvest->altResult,
            'med'    =>  $medInvest->medName,
            'labStatus' => $cashierData->lab_status,
            'altStatus' => $cashierData->alt_status,
        );
        echo json_encode($output);   
    }


    //What we call it again man dude
    function res_postdata(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'result' => 'required',
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

            //Here we set aloop for getting the name of opd number
            $tableName =  Auth::user()->role;
            $tableName =ucfirst($tableName);
            $opdArray = array('Opd1','Opd2','Opd3','Opd4');
            $x = 0;
            while($opdArray[$x] != $tableName)
            {
                $x++;
            }
            $treatedBy = '';
            if($x == 0)
            {
                $student = Opd1::find($request->get('res_student_id'));
                $treatedBy='OPD 1';
            }
            else if($x == 1)
            {
                $student = Opd2::find($request->get('res_student_id'));
                $treatedBy='OPD 2';
            }
            else if($x == 2)
            {
                $student = Opd3::find($request->get('res_student_id'));
                $treatedBy='OPD 3';
            }
            else if($x == 3)
            {
                $student = Opd4::find($request->get('res_student_id'));
                $treatedBy='OPD 4';
            }
                //here is the main code for updating value
                if($request->get('result') == '')
                {
                    $student->result = NULL;
                }
                else
                {
                    $student->result = $request->get('result');
                }
                
                $student->status = 'Finished';
                $student->save();

                $altData = altInvest::find($request->get('res_student_id'));
                $labData = labInvest::find($request->get('res_student_id'));
                $findMedcine = medInvest::find($request->get('res_student_id'));

                $success_output = '<div class="alert alert-success">Updated Successfully</div>';  
        }
        $output = array(
            'error'     =>  $error_array,
            'success'   =>  $success_output
        );
        echo json_encode($output);

    }

    function patient_remove(Request $request)
    {
        $altData = altInvest::find($request->input('id'));
        $labData = labInvest::find($request->input('id'));
        $findMedcine = medInvest::find($request->input('id'));
        //Here we set aloop for getting the name of opd number
        $tableName =  Auth::user()->role;
        $tableName =ucfirst($tableName);
        $opdArray = array('Opd1','Opd2','Opd3','Opd4');
        $x = 0;
        while($opdArray[$x] != $tableName)
        {
            $x++;
        }
        $treatedBy = '';
        if($x == 0)
        {
            $student = Opd1::find($request->input('id'));
            $treatedBy='OPD 1';
        }
        else if($x == 1)
        {
            $student = Opd2::find($request->input('id'));
            $treatedBy='OPD 2';
        }
        else if($x == 2)
        {
            $student = Opd3::find($request->input('id'));
            $treatedBy='OPD 3';
        }
        else if($x == 3)
        {
            $student = Opd4::find($request->input('id'));
            $treatedBy='OPD 4';
        }
        

            $altData = altInvest::find($request->get('id'));
            $labData = labInvest::find($request->get('id'));
            $findMedcine = medInvest::find($request->get('id'));
            $temp=$student->status;

            if(!($altData->altName xor $altData->altResult) && !($labData->labName xor $labData->labResult))
            {
                if($student->result)
                {

                    $histNew = new History;
                    $histNew->id = $request->get('id');
                    $histNew->history = $student->hx;
                    $histNew->dx = $student->dx;
                    $histNew->updated_at = $student->updated_at;
                    $histNew->labName = $student->labName;
                    $histNew->labResult = $student->labResult;
                    $histNew->altName = $student->altName;
                    $histNew->altResult = $student->altResult;
                    $histNew->medName = $student->medName;
                    $histNew->medDoz = $student->medDoz;
                    $histNew->result = $student->result;
                    $histNew->treatedBy = 'At '.$treatedBy.' By: '.Auth::user()->name;
                    $histNew->save();


                    //Here is the code after saving we deleting from all usecases

                    $receptionData = Reception::find($request->get('id'));
                    $receptionData->status = 'Unregistered';
                    $receptionData->save();
                    $finaceCheck = Cashier::find($request->get('id'));
                    //Here we are deleting from lab table
                    $labData->delete();
                    //Here we are deleting from alt table
                    $altData->delete();
                    //Here we are Deleting from opd it self
                    $student->delete();
                    //here we are deleting from cashier table
                    $finaceCheck->delete();
                    $success_output = '<div class="alert alert-success">'.$receptionData->fullName.' successfully cleared from OPD ,Cashier, Laboratory and Ultrasound room !!!</div>';  
                }
                else
                {
                    $success_output = '<div class="alert alert-danger">Unable to clear Patient because you must enter patient result(Diagnosis) !!!</div>'; 
                }
                
            }
            else
            {
                $success_output = '<div class="alert alert-danger">Unable to clear Patient because you must wait for your requested examination result !!!</div>';  
            }
            echo json_encode(['msg'=>$success_output]);
            
    }


    //This method is used for Afete opd types all data about history and diagnosis part
    function postdata(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'hx' => 'required',
            'dx' => 'required',
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

            //Here we set aloop for getting the name of opd number
            $tableName =  Auth::user()->role;
            $tableName =ucfirst($tableName);
            $opdArray = array('Opd1','Opd2','Opd3','Opd4');
            $x = 0;
            while($opdArray[$x] != $tableName)
            {
                $x++;
            }
            $treatedBy = '';
            if($x == 0)
            {
                $student = Opd1::find($request->get('student_id'));
                $treatedBy='OPD 1';
            }
            else if($x == 1)
            {
                $student = Opd2::find($request->get('student_id'));
                $treatedBy='OPD 2';
            }
            else if($x == 2)
            {
                $student = Opd3::find($request->get('student_id'));
                $treatedBy='OPD 3';
            }
            else if($x == 3)
            {
                $student = Opd4::find($request->get('student_id'));
                $treatedBy='OPD 4';
            }

            // if($request->get('button_action') == 'update')
            // {

                //here is the main code for updating value
                $student->hx = $request->get('hx');
                $student->dx = $request->get('dx');
                $student->save();
                $success_output = '<div class="alert alert-success">Updated Successfully</div>';
            
        }
        $output = array(
            'error'     =>  $error_array,
            'success'   =>  $success_output,
            'history' => $request->input('hx'),
            'diagnosis' => $request->input('dx'),
            'errors' => $validation->errors(),
        );
        echo json_encode($output);

    }

        
        
        
    // }

    public function med_fetchdata(Request $request)
    {
        $id = $request->input('id');
        $userName = Reception::find($id);
        $medicine =DB::table('med_finances')->get();
        $finaceCheck = Cashier::find($id);
        //Catagory List fetching
        $medCatagoryList = DB::table('med_finances')
                                ->select('catagory')
                                ->groupBy('catagory')
                                ->get();
        $medCatList ='';
        foreach ($medCatagoryList as $key ) {
            $medCatList .=' , '.$key->catagory;
        }

        //Medicine lists
        $medList = '';
        foreach ($medicine as $med)
        {
            $medList .=' , '.$med->medName;
        }
        $prevData = medInvest::find($id);

        $output = array(
                'medList' => $medList,
                'fullName' => $userName->fullName,
                'prevCat' => $prevData->catagory,
                'prevData' => $prevData->medName,
                'prevDoz' => $prevData->medDoz,
                'prevDuration' =>$prevData->duration,
                'prevFrequency' => $prevData->frequency,
                'finaceCheck' => $finaceCheck->med_status,
                'medCatagoryList' => $medCatList
            );
        echo json_encode($output);
    }
    public function med_postdata(Request $request)
    {
        $intMedicine =$request->get('med_button_action');
        $inputs = array();
        $medicineLists = '';
        $medicineDoze = '';
        $medCatagory = '';
        $medDuration = '';
        $medPerDay = '';
        $checkUniqueMedicine = array();
        $uniqueMedcineBool = '' ;
        for($x=0;$x<=$intMedicine;$x++)
        {
            $inputs['medName'.$x] = 'required|string';
            $inputs['medCatagory'.$x] = 'required|string';
            $inputs['medDuration'.$x] = 'required|string';
            $inputs['medPerDay'.$x] = 'required|string';
            $tempData;
            $no;
            if($request->get('medName'.$x) != '')
            {
                $medNameOnly = explode(' / ', $request->get('medName'.$x));
                $tempData = $medNameOnly[0];
                $uniqueMedcineBool .= array_search($medNameOnly[0], $checkUniqueMedicine);
                $checkUniqueMedicine[$x] = $medNameOnly[0];
                $medRemainDoz = DB::table('med_finances')
                                        ->select('quantity')
                                        ->where('medName',$tempData)
                                        ->get();
                $no = $medRemainDoz[0]->quantity;
                $inputs['medDoz'.$x] = 'required|numeric|min:1|max:'.$no.'';
            }
            else
            {
                $tempData ='';
                $inputs['medDoz'.$x] = 'required|numeric|min:1';
            }
            
            

            if($x == $intMedicine)
            {
                $medicineLists .= $tempData ; 
                $medicineDoze .= $request->get('medDoz'.$x); 
                $medCatagory .= $request->get('medCatagory'.$x); 
                $medDuration .= $request->get('medDuration'.$x); 
                $medPerDay .= $request->get('medPerDay'.$x);   
            }
            else
            {
                $medicineLists .= $tempData.',';
                $medicineDoze .= $request->get('medDoz'.$x).',';
                $medCatagory .= $request->get('medCatagory'.$x).','; 
                $medDuration .= $request->get('medDuration'.$x).','; 
                $medPerDay .= $request->get('medPerDay'.$x).',';  
            }
            

        }
        $validation = Validator::make($request->all(),$inputs);
        $error_array = array();
        $test = '';
        $success_output = '';
        if ($validation->fails() )
        {
            foreach ($validation->messages()->getMessages() as $field_name => $messages)
            {
                $error_array[] ='Please choose medicine name , Enter valid doze amount or Unique medicne name:-'.$request->get('medCatagory0').'-found' ; 
                $test .=' duration:-'.$request->get('medDuration0').'-found and freequency-'.$request->get('medPerDay0').'found' ; 
            }
        }
        else if($uniqueMedcineBool || $uniqueMedcineBool == '0')
        {
            $error_array[] ='Please choose medicine name , Enter valid doze amount or Uniqu'.$uniqueMedcineBool ; 
        }
        else
        {
            $medUpdate = medInvest::find($request->get('med_student_id'));
            $medUpdate->medName = $medicineLists;
            $medUpdate->medDoz = $medicineDoze;
            $medUpdate->catagory = $medCatagory;
            $medUpdate->duration  = $medDuration;
            $medUpdate->frequency = $medPerDay;
            $medUpdate->save();
            $success_output = '<div class="alert alert-success">Successfully saved</div>';
        }
        $id = $request->get('med_student_id');
        // if($checkUniqueMedicine != '')
        // {
        //     $error_array[] = 'has error';
        // }
        $output =array(
            'errorCheck' => $validation->errors(),
            'datas' => $test,
            'error' => $error_array,
            'success' => $success_output,
            'bool'=> 'A'.$uniqueMedcineBool.'Z',
        );
        echo json_encode($output);
    }

    function catagory(Request $request)
    {
        $medCatagory = DB::table('med_finances')
                            ->select('medName','quantity')
                            ->where('catagory',$request->get('value'))
                            ->get();
        $medList = array();
        foreach ($medCatagory as $key )
         {
            $medList[] = $key->medName.' / '.$key->quantity;
            //├├├├├├
          }                            
        $output = array('medList' => $medList);
        echo json_encode($output);
    }

    function labFetchData(Request $request)
    {
        $id = $request->get('id');
        $receptionsData = Reception::find($id);
        $patientData = labInvest::find($id);
        $cashier = Cashier::find($id);
        $labStatus = $cashier->lab_status;

        $totalData = array();
        $labFinance = DB::table('lab_finances')
                                    ->select('catagory')
                                    ->groupBy('catagory')
                                    ->get();
        $labCatagories = array();
        foreach ($labFinance as $key => $value) {
            $labCatagories[] = $value;
        }

        foreach ($labCatagories as $key => $value) {

            $labList = DB::table('lab_finances')
                            ->select('labName')
                            ->where('catagory',$value->catagory)
                            ->get();
            $allLabList =array();                            
            foreach ($labList as $labKey => $labValue) {
                $allLabList[] = $labValue->labName;
            }
            $totalData[$value->catagory] = $allLabList;                            
                                        
        }



        $output = array('labName' =>$patientData->labName ,'catagory'=>$patientData->catagory,'total'=>$totalData ,'labStatus'=>$labStatus ,'fullName'=>$receptionsData->fullName,'age'=>$receptionsData->age,'type'=>$receptionsData->type);

        echo json_encode($output);
    }



    function labCatList(Request $request)
    {
        $cat = $request->get('cat');
        $labTests = DB::table('lab_finances')
                            ->select('labName')
                            ->where('catagory',$cat)
                            ->get();
        $arrayName = array();
        foreach ($labTests as $key ) {
            $arrayName[] = $key->labName;
        }
        $output  = array('labTest' => $arrayName );
        echo json_encode($output);

    }

    function labPostData(Request $request)
    {
        $userID = $request->get('userID');
        $numberOfData = $request->get('countInput');
        $selectedCatagories = array();
        for ($i=0; $i <= $numberOfData; $i++) { 
           $selectedCatagories[] = $request->get('labCatID'.$i);
        }
        if(count($selectedCatagories) == count(array_unique($selectedCatagories)) )
        {
            $labDB = labInvest::find($userID);
            $labListStore = '';
            $catListStore = '';
            $temp = '';
            foreach ($selectedCatagories as $catKey => $catValue) 
            {
                if($catKey == '0')
                {
                    $catListStore .= ''.$catValue.'';
                }
                else
                {
                    $labListStore .='&';
                    $catListStore .=','.$catValue.'';
                }

                $labList = DB::table('lab_finances')
                            ->select('labName')
                            ->where('catagory',$catValue)
                            ->get();
                $length = count($labList);
                $length--;
                $temp3 = array();
                foreach ($labList as $labKey => $labValue) 
                {
                    $temp2 = $catKey.'lab'.$labKey.'';
                    if($request->has($temp2))
                    {
                        if(count($temp3) == 0)
                        {
                            $temp3[] = $request->get($temp2);
                            $labListStore .= $request->get($temp2);
                        }
                        else
                        {
                            $labListStore .= ','.$request->get($temp2);
                        }

                    }
                }
            }
            $labDB->catagory = $catListStore;
            $labDB->labName = $labListStore;
            $error='';
            $success='';
            if(count($temp3))
            {
                 $labDB->save();
                 $success = "<div class='alert alert-success alert-dismissible'>Laboratory examination saved Successfully !!!</div>";
            }
            else if(count($selectedCatagories) == 1)
            {
                if($selectedCatagories[0] == '')
                {
                    $labDB->catagory = NULL;
                    $labDB->labName = NULL;
                    $labDB->save();
                    $success = "<div class='alert alert-success alert-dismissible'>Laboratory examination saved Successfully !!!</div>";
                }
            }
            else
            {
                $error = "<div class='alert alert-danger alert-dismissible'>You have to check atleast one in each catagory !!!</div>";
            }
            

            $output = array('success'=> $success,'error' => $error);
            echo json_encode($output);
        }
        else
        {
            $catErrors = "<div class='alert alert-danger alert-dismissible'>There are dupblicated catagories values</div>";
            $arrayName = array('error' => $catErrors );
            echo json_encode($arrayName);
        }
        
    }





    function altFetchData(Request $request)
    {
        $id = $request->get('id');
        $receptionsData = Reception::find($id);
        $patientData = altInvest::find($id);
        $cashier = Cashier::find($id);
        $altStatus = $cashier->alt_status;

        $totalData = array();
        $altFinance = DB::table('alt_finances')
                                    ->select('catagory')
                                    ->groupBy('catagory')
                                    ->get();
        $altCatagories = array();
        foreach ($altFinance as $key => $value) {
            $altCatagories[] = $value;
        }

        foreach ($altCatagories as $key => $value) {

            $altList = DB::table('alt_finances')
                            ->select('altName')
                            ->where('catagory',$value->catagory)
                            ->get();
            $allaltList =array();                            
            foreach ($altList as $altKey => $altValue) {
                $allaltList[] = $altValue->altName;
            }
            $totalData[$value->catagory] = $allaltList;                            
                                        
        }



        $output = array('altName' =>$patientData->altName ,'catagory'=>$patientData->catagory,'total'=>$totalData ,'altStatus'=>$altStatus,'fullName'=>$receptionsData->fullName,'age'=>$receptionsData->age,'type'=>$receptionsData->type);

        echo json_encode($output);
    }


    function altCatList(Request $request)
    {
        $cat = $request->get('cat');
        $altTests = DB::table('alt_finances')
                            ->select('altName')
                            ->where('catagory',$cat)
                            ->get();
        $arrayName = array();
        foreach ($altTests as $key ) {
            $arrayName[] = $key->altName;
        }
        $output  = array('altTest' => $arrayName );
        echo json_encode($output);

    }

    function altPostData(Request $request)
    {
        $userID = $request->get('alt_userID');
        $numberOfData = $request->get('alt_countInput');
        $selectedCatagories = array();
        for ($i=0; $i <= $numberOfData; $i++) { 
           $selectedCatagories[] = $request->get('altCatID'.$i);
        }
        if(count($selectedCatagories) == count(array_unique($selectedCatagories)) )
        {
            $altDB = altInvest::find($userID);
            $altListStore = '';
            $catListStore = '';
            $temp = '';
            foreach ($selectedCatagories as $catKey => $catValue) 
            {
                if($catKey == '0')
                {
                    $catListStore .= ''.$catValue.'';
                }
                else
                {
                    $altListStore .='&';
                    $catListStore .=','.$catValue.'';
                }

                $altList = DB::table('alt_finances')
                            ->select('altName')
                            ->where('catagory',$catValue)
                            ->get();
                $length = count($altList);
                $length--;
                $temp3 = array();
                foreach ($altList as $altKey => $altValue) 
                {
                    $temp2 = $catKey.'alt'.$altKey.'';
                    if($request->has($temp2))
                    {
                        if(count($temp3) == 0)
                        {
                            $temp3[] = $request->get($temp2);
                            $altListStore .= $request->get($temp2);
                        }
                        else
                        {
                            $altListStore .= ','.$request->get($temp2);
                        }

                    }
                }
            }
            
            $error='';
            $success='';
            if(count($temp3))
            {
                $altDB->catagory = $catListStore;
                $altDB->altName = $altListStore;
                $altDB->save();
                $success = "<div class='alert alert-success alert-dismissible'>Altrasound examination saved Successfully !!!</div>";
            }
            else if(count($selectedCatagories) == 1)
            {
                if ($selectedCatagories[0] == '') {
                    $altDB->catagory = NULL;
                    $altDB->altName = NULL;
                    $altDB->save();
                    $success = "<div class='alert alert-success alert-dismissible'>Altrasound examination saved Successfully !!!</div>";
                }
            }
            else
            {
                $error = "<div class='alert alert-danger alert-dismissible'>You have to check atleast one in each catagory !!!</div>";
            }
            

            $output = array('success'=> $success,'error' => $error);
            echo json_encode($output);
        }
        else
        {
            $catErrors = "<div class='alert alert-danger alert-dismissible'>There are dupblicated catagories values</div>";
            $arrayName = array('error' => $catErrors );
            echo json_encode($arrayName);
        }
        
    }

    function patientRecord()
    {
        return view('opd/patientRecord');
    }
    function pRecordGetData()
    {

        $patientList = Reception::all();
        foreach ($patientList as $key => $value) {
            $updatePatient= Reception::find($patientList[$key]->id);
            // $updatePatient->dateBefore = $updatePatient->updated_at->diffForHumans();
            // $updatePatient->save();
            DB::table('receptions')
                    ->where('id',$patientList[$key]->id)
                    ->update([
                                'dateBefore' => $updatePatient->updated_at->diffForHumans()
                            ]);
        }


        $patient = DB::table('receptions')
                       ->select('id', 'fullName', 'age', 'type','gender','subcity','dateBefore')
                       ->get();
        return DataTables::of($patient)
               ->addColumn('action', function($patient){
                   return '<a href="../history?id='.$patient->id.'&page=1" class="btn btn-xs btn-primary history" id="'.$patient->id.'" target="_blank"><i class="glyphicon glyphicon-book"></i> History</a>  ';
               })
               ->make(true);
    }
}

<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Cashier;
use App\Reception;
use App\patientType;
use App\opd;
use App\labInvest;
use App\labFinance;
use App\altInvest;
use App\medInvest;
use App\medFinance;
use DataTables;
use Gate;
use DB;
use Carbon\Carbon;


class CashierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Gate::allows('isCashier'))
        {
            return redirect('/');
        }
        //Here we will allocate to the view which redirect  to
        $today = date('Y-m-d');
        $cashierReport = DB::table('receptions')
                            ->select(DB::raw('SUM(med_total_fee) as medT, SUM(alt_total_fee) as altT,sum(lab_total_fee) as labT, sum(total_fee) as cardT, count(*) as sum,sum(med_net_fee) as medN,sum(alt_net_fee) as altN,sum(lab_net_fee) as labN, sum(net_fee) as cardN'))
                            ->where('updated_at','like',$today.'%')
                            ->get();
        $totalFee = $cashierReport[0]->medT+$cashierReport[0]->altT+$cashierReport[0]->labT+$cashierReport[0]->cardT;
        $netFee = $cashierReport[0]->medN+$cashierReport[0]->altN+$cashierReport[0]->labN+$cashierReport[0]->cardN;
        return view('cashier/cashier')->with(['numer'=>$cashierReport[0]->sum,'totalFee'=>$totalFee,'netFee'=>$netFee]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getdata()
    {
         $patient = DB::table('cashiers')
                        ->join('receptions', 'cashiers.id', '=', 'receptions.id')
                        ->select( 'receptions.fullName', 'receptions.phone', 'receptions.opd_num', 'receptions.type','cashiers.*')
                        ->orderBy('cashiers.id','desc')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    if($patient->status == 'Unpaid')
                    {
                        return ' <a href="#" class="btn btn-xs btn-success delete" id="'.$patient->id.'" ><i class="glyphicon glyphicon-ok"></i> '.$patient->status.'</a>';
                    }
                    else
                    {
                        return '<a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->status.'</a>';
                    }
                })
                ->setRowClass(function ($patient) {
                    return $patient->status == 'Unpaid' ? 'alert-success' : '';
                })
                ->make(true);
    }


    function fetchdata(Request $request)
    {
        $id = $request->input('id');
        $student = Cashier::find($id);
        $pastTime = $student->updated_at;
        $diffInDay = Carbon::parse(Carbon::now())->diffInDays($pastTime);
        $output = array(
            'total_fee'    =>  $student->total_fee,
            'dateDiff'   => $diffInDay,
        );
        echo json_encode($output);
    }




    function removedata(Request $request)
    {
        $student = Cashier::find($request->input('id'));
        $student->status ='Paid';
        $findOPD = Reception::find($request->input('id'));
        $getOPD = $findOPD->opd_num;
        $getOPD .='s';
        $med =DB::table('med_invests')->insert(
                    ['id' => $student->id,
                      'created_at' => $student->freshTimestamp() ]
                    );
        $opd =DB::table($getOPD)->insert(
                    ['id' => $student->id,
                      'created_at' => $student->freshTimestamp() ]
                    );
        $lab =DB::table('lab_invests')->insert(
                    ['id' => $student->id,
                      'created_at' => $student->freshTimestamp() ]
                    );
        $alt =DB::table('alt_invests')->insert(
                    ['id' => $student->id,
                      'created_at' => $student->freshTimestamp() ]
                    );
        $output =array(
                'success' => '<div class="alert alert-success">'.$findOPD->fullName.' Successfully Transferred to '.$findOPD->opd_num.'</div> ',
            );
        if($student->save() && $med && $opd && $lab && $alt)
        {
            echo json_encode($output);
        }
        else
        {
            echo json_encode('Error ocured');
        }
            
    }



    //*****************Laboratory page*****************//
    //Here we declare for laboratory page
    public function labGetdata()
    {
         $patient = DB::table('cashiers')
                        ->join('receptions', 'cashiers.id', '=', 'receptions.id')
                        ->join('lab_invests','receptions.id','=','lab_invests.id')
                        ->select( 'receptions.fullName', 'receptions.phone', 'receptions.opd_num', 'receptions.type','cashiers.id','cashiers.lab_total_fee','cashiers.lab_discount_fee','cashiers.lab_net_fee','cashiers.lab_status')
                        ->where('lab_invests.labName','!=','')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    if($patient->lab_status == 'Unpaid' && $patient->lab_total_fee != '')
                    {
                        return '<a href="#" class="btn btn-xs btn-primary labEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-eye-open"></i> View</a> <a href="#" class="btn btn-xs btn-success labPayement" id="'.$patient->id.'"><i class="glyphicon glyphicon-ok"></i> '.$patient->lab_status.'</a>';
                    }
                    else
                    {
                        return '<a href="#" class="btn btn-xs btn-primary labEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-eye-open"></i> View</a> <a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->lab_status.'</a>';
                    }
                    
                })

                ->setRowClass(function ($patient) {
                    return $patient->lab_status == 'Unpaid' ? 'alert-success' : 'alert-warning';
                })
                ->make(true);
    }

    //Over here i will declare what will be fetched when lab edit button is clicked
    function labFetchdata(Request $request)
    {
        //Here we save the id from input
        $id = $request->input('id');
        $student = labInvest::find($id);
        $userType = Reception::find($id);
        $userTypeName = $userType->type;
        $patientName = $userType->fullName;
        $typePercent = DB::table('patient_types')
                        ->select( 'fee')
                        ->where('name','=',$userTypeName)
                        ->get();
        $typePercentDiscount =$typePercent[0]->fee;
        //We will secure our application if the enter invalid id through jquery
        if($student == null)
        {
            return back();
        }
        $output = array(
            'labName'    =>  $student->labName,
        );
        //After we list lab test over output we should put in $lablist as stirng
        $labList = $output['labName'];
        //The string we get in above should explode 
        $labFirstList = explode('&',$labList);
        $string = '';
        foreach ($labFirstList as $key => $value) 
        {
            if($key == 0)
            {
                $string .=$value;
            }
            else
            {
                $string .=','.$value;
            }
           
        }

        $labList = explode(',',$string);
        
        //Here we define a var as new for storing exploded value as object
        $new = array();
        //The above array lablist will be fetched and saved to $new array
        $priceTotalFinal = 0;
        $priceDiscountFinal =0;
        $priceNetFinal = 0;
        foreach ($labList as $key  )
        {
        //Here we fetch the price of each lab and save it in $new object variable
            $labFee = DB::table('lab_finances')
                        ->select( 'price')
                        ->where('labName','=',$key)
                        ->get();
        //Since lab fee is associative array we should give index and key
            $priceTotal = $labFee[0]->price;
            $priceTotalFinal += $priceTotal;
        //Let we Calculate the discount from total
            $priceDiscount = $priceTotal * $typePercentDiscount;
            $priceDiscountFinal  += $priceDiscount;
        //Let we calculate the net fee
            $priceNet = $priceTotal - $priceDiscount;
            $priceNetFinal += $priceNet;
        //price and now we give labTest name as $key and Price as $priceTotal
            $new[$key] = "$key,$priceTotal,$priceDiscount,$priceNet,
                                $priceTotalFinal,$priceDiscountFinal,$priceNetFinal";

        }
        //Let we save data to labFinance table
        $saveFinance = Cashier::find($id);
        $saveFinance->lab_total_fee =$priceTotalFinal ;
        $saveFinance->lab_discount_fee = $priceDiscountFinal;
        $saveFinance->lab_net_fee = $priceNetFinal;
        $saveFinance->save();
        //We have to send object array $new again object array  
        $juju=array("datas"=>$new,"name"=>$patientName);
        //We have to send data as json_encode
        echo json_encode($juju);
    }

    function labPayement(Request $request)
    {

        $id = $request->input('id');
        $student = labInvest::find($id);
        $userType = Reception::find($id);
        $userTypeName = $userType->type;
        $patientName = $userType->fullName;
        $typePercent = DB::table('patient_types')
                        ->select( 'fee')
                        ->where('name','=',$userTypeName)
                        ->get();
        $typePercentDiscount =$typePercent[0]->fee;
        //We will secure our application if the enter invalid id through jquery
        if($student == null)
        {
            return back();
        }
        $output = array(
            'labName'    =>  $student->labName,
        );
        //After we list lab test over output we should put in $lablist as stirng
        $labList = $output['labName'];
        //The string we get in above should explode 
        $labFirstList = explode('&',$labList);
        $string = '';
        foreach ($labFirstList as $key => $value) 
        {
            if($key == 0)
            {
                $string .=$value;
            }
            else
            {
                $string .=','.$value;
            }
           
        }

        $labList = explode(',',$string);
        
        //Here we define a var as new for storing exploded value as object
        $new = array();
        //The above array lablist will be fetched and saved to $new array
        $priceTotalFinal = 0;
        $priceDiscountFinal =0;
        $priceNetFinal = 0;
        foreach ($labList as $key  )
        {
        //Here we fetch the price of each lab and save it in $new object variable
            $labFee = DB::table('lab_finances')
                        ->select( 'price')
                        ->where('labName','=',$key)
                        ->get();
        //Since lab fee is associative array we should give index and key
            $priceTotal = $labFee[0]->price;
            $priceTotalFinal += $priceTotal;
        //Let we Calculate the discount from total
            $priceDiscount = $priceTotal * $typePercentDiscount;
            $priceDiscountFinal  += $priceDiscount;
        //Let we calculate the net fee
            $priceNet = $priceTotal - $priceDiscount;
            $priceNetFinal += $priceNet;
        //price and now we give labTest name as $key and Price as $priceTotal
            $new[$key] = "$key,$priceTotal,$priceDiscount,$priceNet,
                                $priceTotalFinal,$priceDiscountFinal,$priceNetFinal";

        }
        //Let we save data to labFinance table
        $saveFinance = Cashier::find($id);
        $fullName = Reception::find($request->get('id'));
        $success=array();
        $error=array();
        if($saveFinance->lab_total_fee == $priceTotalFinal && 
           $saveFinance->lab_discount_fee == $priceDiscountFinal &&
           $saveFinance->lab_net_fee == $priceNetFinal)
        {
            $student = Cashier::find($request->input('id'));
            $student->lab_status ='Paid';
            $student->save();
            $success = $fullName->fullName."transferred to opd room successfully!!!";
        }
        else
        {
            $errors = $fullName->fullName." has some chenges please review it !!!";
        }
        $output = array('errors'=>$errors,'success'=>$success);
        
        echo json_encode(['errors'=>$errors]);
            
    }

    //||||||||||||||||||||||||||| START: FETCHING ALTRASOUND DATA |||||||||||||||||||||
    public function altGetdata()
    {
         $patient = DB::table('cashiers')
                        ->join('receptions', 'cashiers.id', '=', 'receptions.id')
                        ->join('alt_invests','receptions.id','=','alt_invests.id')
                        ->select( 'receptions.fullName', 'receptions.phone', 'receptions.opd_num', 'receptions.type','cashiers.id','cashiers.alt_total_fee','cashiers.alt_discount_fee','cashiers.alt_net_fee','cashiers.alt_status')
                        ->where('alt_invests.altName','!=','')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    if($patient->alt_status == 'Unpaid' && $patient->alt_total_fee != '')
                    {
                        return '<a href="#" class="btn btn-xs btn-primary altEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-eye-open"></i> View</a> <a href="#" class="btn btn-xs btn-success altPayement" id="'.$patient->id.'"><i class="glyphicon glyphicon-ok"></i> '.$patient->alt_status.'</a>';
                    }
                    else
                    {
                        return '<a href="#" class="btn btn-xs btn-primary altEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-eye-open"></i> View</a> <a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->alt_status.'</a>';
                    }
                    
                })
                ->make(true);
    }
    //For Editing purpose we use this code
    function altFetchdata(Request $request)
    {
        //Here we save the id from input
        $id = $request->input('id');
        $student = altInvest::find($id);
        $userType = Reception::find($id);
        $userTypeName = $userType->type;
        $patientName = $userType->fullName;
        $typePercent = DB::table('patient_types')
                        ->select( 'fee')
                        ->where('name','=',$userTypeName)
                        ->get();
        $typePercentDiscount =$typePercent[0]->fee;
        //We will secure our application if the enter invalid id through jquery
        if($student == null)
        {
            return back();
        }
        $output = array(
            'altName'    =>  $student->altName,
        );
        //After we list lab test over output we should put in $lablist as stirng
        $altList = $output['altName'];
        $temp = explode('&', $altList);
        $temp2 = '';
        foreach ($temp as $key => $value) {
            if( $key != 0)
            {
                $temp2 .=','.$temp[$key];
            }
            else
            {
                $temp2 .=$temp[$key];
            }
        }
        //The string we get in above should explode 
        $altList = explode(',',$temp2);
        //Here we define a var as new for storing exploded value as object
        $new = array();
        //The above array lablist will be fetched and saved to $new array
        $priceTotalFinal = 0;
        $priceDiscountFinal =0;
        $priceNetFinal = 0;
        foreach ($altList as $key  )
        {
        //Here we fetch the price of each lab and save it in $new object variable
            $altFee = DB::table('alt_finances')
                        ->select( 'price')
                        ->where('altName','=',$key)
                        ->get();
        //Since lab fee is associative array we should give index and key
            $priceTotal = $altFee[0]->price;
            $priceTotalFinal += $priceTotal;
        //Let we Calculate the discount from total
            $priceDiscount = $priceTotal * $typePercentDiscount;
            $priceDiscountFinal  += $priceDiscount;
        //Let we calculate the net fee
            $priceNet = $priceTotal - $priceDiscount;
            $priceNetFinal += $priceNet;
        //price and now we give labTest name as $key and Price as $priceTotal
            $new[$key] = "$key,$priceTotal,$priceDiscount,$priceNet,
                                $priceTotalFinal,$priceDiscountFinal,$priceNetFinal";


        }
        //Let we save data to labFinance table
        $saveFinance = Cashier::find($id);
        $saveFinance->alt_total_fee =$priceTotalFinal ;
        $saveFinance->alt_discount_fee = $priceDiscountFinal;
        $saveFinance->alt_net_fee = $priceNetFinal;
        $saveFinance->save();
        //We have to send object array $new again object array  
        $juju=array("datas"=>$new,"name"=>$patientName);
        //We have to send data as json_encode
        echo json_encode($juju);
    }

    //Alt payment operating system
    function altPayement(Request $request)
    {
        $student = Cashier::find($request->input('id'));
        $userName = Reception::find($request->input('id'));
        $student->alt_status ='Paid';
        $user=$userName->fullName;
        if($student->save())
        {
            $output = array(
                            'success'   =>  '<div class="alert alert-success">'.$user.' Successfully transfered to altrasound room</div>'
                        );
            echo json_encode($output);
        }
        else
        {
            $output = array(
                            'success'   =>  '<div class="alert alert-danger">Not transfered to altrasound room</div>'
                        );
            echo json_encode($output);
        }
            
    }
    //||||||||||||||||||||||||ENDS: ALTRASOUND ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


    //||||||||||||||||||||||||START: MEDICINE ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    public function medGetdata()
    {
         $patient = DB::table('cashiers')
                        ->join('receptions', 'cashiers.id', '=', 'receptions.id')
                        ->join('med_invests','receptions.id','=','med_invests.id')
                        ->select( 'receptions.fullName', 'receptions.phone', 'receptions.opd_num', 'receptions.type','cashiers.id','cashiers.med_total_fee','cashiers.med_discount_fee','cashiers.med_net_fee','cashiers.med_status')
                        ->where('med_invests.medName','!=','')
                        ->get();
         return DataTables::of($patient)
                ->addColumn('action', function($patient){
                    if($patient->med_status == 'Unpaid' && $patient->med_total_fee != '')
                    {
                        return '<a href="#" class="btn btn-xs btn-primary medEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-eye-open"></i> View</a> <a href="#" class="btn btn-xs btn-success medPayement" id="'.$patient->id.'"><i class="glyphicon glyphicon-ok"></i> '.$patient->med_status.'</a>';
                    }
                    else
                    {
                        return '<a href="#" class="btn btn-xs btn-primary medEdit" id="'.$patient->id.'"><i class="glyphicon glyphicon-eye-open"></i> View</a> <a href="#" class="btn btn-xs btn-success " id="'.$patient->id.'" disabled><i class="glyphicon glyphicon-ok"></i> '.$patient->med_status.'</a>';
                    }
                    
                })
                ->make(true);
    }
    //For Editing purpose we use this code
    function medFetchdata(Request $request)
    {
        //Here we save the id from input
        $id = $request->input('id');
        $student = medInvest::find($id);
        $userType = Reception::find($id);
        $userTypeName = $userType->type;
        $patientName = $userType->fullName;
        $typePercent = DB::table('patient_types')
                        ->select( 'fee')
                        ->where('name','=',$userTypeName)
                        ->get();
        $typePercentDiscount =$typePercent[0]->fee;
        //We will secure our application if the enter invalid id through jquery
        if($student == null)
        {
            return back();
        }
        $output = array(
            'medName'    =>  $student->medName,
            'medDoz' => $student->medDoz
        );
        //After we list lab test over output we should put in $lablist as stirng
        $medList = $output['medName'];
        //The string we get in above should explode 
        $medList = explode(',',$medList);

        $medDoz = $output['medDoz'];
        $medDoz = explode(',', $medDoz);
        //Here we define a var as new for storing exploded value as object
        $new = array();
        //The above array lablist will be fetched and saved to $new array
        $priceTotalFinal = 0;
        $priceDiscountFinal =0;
        $priceNetFinal = 0;
        $count = 0;
        foreach ($medList as $key  )
        {
        //Here we fetch the price of each lab and save it in $new object variable
            $medFee = DB::table('med_finances')
                        ->select( 'price')
                        ->where('medName','=',$key)
                        ->get();

            $medDozCount = $medDoz[$count];
            $count++;
        //Since lab fee is associative array we should give index and key
            $singlePrice = $medFee[0]->price;
            $priceTotal = $medFee[0]->price * $medDozCount;
            $priceTotalFinal += $priceTotal ;

        //Let we Calculate the discount from total
            $priceDiscount = $priceTotal * $typePercentDiscount;
            $priceDiscountFinal  += $priceDiscount;
        //Let we calculate the net fee
            $priceNet = $priceTotal - $priceDiscount;
            $priceNetFinal += $priceNet;
        //price and now we give labTest name as $key and Price as $priceTotal
            $new[$key] = "$key,$priceTotal,$priceDiscount,$priceNet,$priceTotalFinal,$priceDiscountFinal,$priceNetFinal,$medDozCount,$singlePrice";


        }
        //Let we save data to labFinance table
        $saveFinance = Cashier::find($id);
        $saveFinance->med_total_fee =$priceTotalFinal ;
        $saveFinance->med_discount_fee = $priceDiscountFinal;
        $saveFinance->med_net_fee = $priceNetFinal;
        $saveFinance->save();
        //We have to send object array $new again object array  
        $juju=array("datas"=>$new,"name"=>$patientName);
        //We have to send data as json_encode
        echo json_encode($juju);
    }

    //Alt payment operating system
    function medPayement(Request $request)
    {
        $student = Cashier::find($request->input('id'));
        $userName = Reception::find($request->input('id'));
        $student->med_status ='Paid';
        $user=$userName->fullName;

        $medicineDozeFunction = medInvest::find($request->input('id'));
        $getList = array('medName' => $medicineDozeFunction->medName,'medDoz'=>$medicineDozeFunction->medDoz );
        $medNameList = $getList['medName'];
        $medNameList = explode(',', $medNameList);
        $medDozList = $getList['medDoz'];
        $medDozList = explode(',', $medDozList);
        foreach ($medNameList as $key => $value) 
        {
            $alteredMedicine = DB::table('med_finances')
                                    ->select('quantity','id')
                                    ->where('medName',$value)
                                    ->get();
            $savingData = medFinance::find($alteredMedicine[0]->id);
            $savingData->quantity = $savingData->quantity -  $medDozList[$key];
            $savingData->save(); 
        }




        if($student->save())
        {
            $output = array(
                            'success'   =>  '<div class="alert alert-success">'.$user.' Successfully transfered to Pharmacy room</div>'
                        );
            echo json_encode($output);
        }
        else
        {
            $output = array(
                            'success'   =>  '<div class="alert alert-danger">Not transfered to Pharmacy room</div>'
                        );
            echo json_encode($output);
        }
            
    }

    //||||||||||||||||||||||||ENDS: MEDICINE |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reception;
use App\History;
use App\User;
use Auth;
use DB;
use App\Exports\HistoriesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

//use Excel;

class ReportController extends Controller
{
    //
    public function index()
    {
        $whoami = ''.Auth::user()->role.'';
        if($whoami == 'opd1')
        {
            $whoami = 'OPD 1';
            $patientList = History::where('treatedBy', $whoami)->get();
        }
        else if($whoami == 'opd2')
        {
            $whoami = 'OPD 2';
            $patientList = History::where('treatedBy', $whoami)->get();
        }
        else if($whoami == 'opd3')
        {
            $whoami = 'OPD 3';
            $patientList = History::where('treatedBy', $whoami)->get();
        }
        else if($whoami == 'opd4')
        {
            $whoami = 'OPD 4';
            $patientList = History::where('treatedBy', $whoami)->get();
        }
        else
        {
            $patientList = History::all();
        }
    	$num=count($patientList);
    	$commonData = Reception::all();
        $employList = User::all();
    	return view('report.opd')->with(['list'=>$patientList,'common'=>$commonData,'count'=>$num,'users'=>$employList]);
    }
    public function refresh(Request $request)
    {
        $whoami = ''.Auth::user()->role.'';
        if($whoami == 'opd1')
        {
            $whoami = 'OPD 1';
            $patientList = History::where('treatedBy', $whoami)
                                ->whereBetween('date',[$request->get('startDate'),$request->get('endDate')])
                                ->get();
        }
        else if($whoami == 'opd2')
        {
            $whoami = 'OPD 2';
            $patientList = History::where('treatedBy', $whoami)
                                ->whereBetween('date',[$request->get('startDate'),$request->get('endDate')])
                                ->get();
        }
        else if($whoami == 'opd3')
        {
            $whoami = 'OPD 3';
            $patientList = History::where('treatedBy', $whoami)
                                ->whereBetween('date',[$request->get('startDate'),$request->get('endDate')])
                                ->get();
        }
        else if($whoami == 'opd4')
        {
            $whoami = 'OPD 4';
            $patientList = History::where('treatedBy', $whoami)
                                ->whereBetween('date',[$request->get('startDate'),$request->get('endDate')])
                                ->get();
        }
        else
        {
            $patientList = History::whereBetween('date',[$request->get('startDate'),$request->get('endDate')])
                                ->get();
        }
        
        $num=count($patientList);
        $commonData = Reception::all();
        if($request->get('listType') == 'pList')
        {
            $output = array(
                            'success'=>'hello world',
                            'num' => $num,
                            'common' => $commonData,
                            'list' => $patientList,
                            );
            echo json_encode($output);
            
    	}
        else
        {
            $counter = array();
            $labCounter = array();
            $altCounter = array();
            foreach ($patientList as $key => $value) 
            {
                //It is medicine list operation to do
                $medValue = $value->medName;
                $medDozValue = $value->medDoz;
                $medValue = explode(',', $medValue);
                $medDozValue = explode(',', $medDozValue);
                foreach ($medValue as $k => $care) 
                {
                    if(array_key_exists($care, $counter))
                    {
                        $counter[$care] = $counter[$care] + $medDozValue[$k];
                    }
                    else
                    {
                        if($care != '')
                        {
                          $counter[$care] = $medDozValue[$k];  
                        }
                    }
                }

                //It is for laboratory list operation
                $labValue = $value->labName;
                $temp1 = explode('&', $labValue);
                $temp2 = '';
                foreach ($temp1 as $i => $indexValue) {
                    if($i != 0)
                        $temp2 .=','.$indexValue;
                    else
                        $temp2 .=$indexValue;
                }
                $labValue = explode(',', $temp2);
                foreach ($labValue as $k => $care) {
                    if (array_key_exists($care, $labCounter)) 
                    {
                        $labCounter[$care]++;
                    }
                    else
                    {
                        if($care !='')
                        {
                            $labCounter[$care] = 1;
                        }
                    }
                }

                //It is for altrasound list operation
                $altValue = $value->altName;
                $temp3 = explode('&', $altValue);
                $temp4 = '';
                foreach ($temp3 as $kk => $kval) {
                    if($kk != 0)
                        $temp4.=','.$kval;
                    else
                        $temp4 = $kval;
                }
                $altValue = explode(',', $temp4);
                foreach ($altValue as $k => $care) 
                {
                    if(array_key_exists($care, $altCounter))
                    {
                        $altCounter[$care]++;
                    }
                    else
                    {
                        if($care != '')
                        {
                            $altCounter[$care] = 1;
                        }
                    }
                }
            }
            $output = array('counter'=>$counter,'labCounter'=>$labCounter,'altCounter'=>$altCounter);
            echo json_encode($output);
        }
    }
    function excel()
    {
        return Excel::download(new HistoriesExport, 'Report.xlsx');
    }
}

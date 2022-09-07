<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\employe;
use App\user;
use App\Reception;
use App\labFinance;
use DataTables;
use Gate;
use DB;
use Excel;
use PDF;
use Validator;

class AdminController extends Controller
{
    


    public function index()
    {
        //This line of code is used to authenticate user
        if (!Gate::allows("isAdmin")) {
            //abort(403,"Sorry, You can do this actions");
            return redirect('/');
        }
        return view('admin.addEmploye');
    }

    public function online()
    {
        $users= User::all();
        return view('report.online')->with(['users'=>$users]);
    }
    public function registerSubmit(Request $request )
    {
        $validation = Validator::make($request->all(),[
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required'
        ]);
        $error_array = array( );
        $success='';
        if($validation->fails())
        {
            foreach ($validation->Messages()->getMessages() as $key => $value) {
                $error_array[] = $value;
            }
        }
        else
        {
            $User = new User;
            $User->name = $request->get('name');
            $User->email = $request->get('email');
            $User->role = $request->get('role');
            $User->password = bcrypt($request->get('password'));
            $User->save();
            $success = '<div class="alert alert-success">Successfully Added!!!</div>';
        }
        $output = array('success' =>  $success,'error' => $error_array);
        echo json_encode($output);
    }
    public function empList()
    {
        if (!Gate::allows("isAdmin")) {
            //abort(403,"Sorry, You can do this actions");
            return redirect('/');
        }
        return view('admin.employes');
    }

    function getdata()
    {
        $students = User::select('id', 'name','role','email','updated_at')->orderBy('role','desc')->get();
        return DataTables::of($students)
                            ->addColumn('action',function($students){
                                return '<button id="'.$students->id.'" class="btn btn-primary btn-xs edit"><span class="glyphicon glyphicon-edit"></span> Edit </button> <button id="'.$students->id.'" class="btn btn-danger btn-xs delete"><span class="glyphicon glyphicon-remove"></span> Delete</button>';
                            })
                            ->make(true);
    }

    function delete(Request $request)
    {
        $delete = DB::table('users')
            ->where('id',$request->input('id'))
            ->delete();
        if($delete)
        {
            $output = array("success"=>"Successfully deleted!!!");
        }
        else
        {
            $output = array("success" => "Not deleted!!!");
        }
        echo json_encode($output);
    }

    function fetchdata(Request $request)
    {
        $employeList = User::find($request->input('id'));
        $output = array('id'=>$employeList->id,'name'=>$employeList->name,'role'=>$employeList->role);
        echo json_encode($output);
    }

    function postdata(Request $request)
    {  
        $employe = User::find($request->get('user_id'));
        $employe->role = $request->get('role');
        $employe->save();
        //echo json_encode('helll');
        return redirect('/admin/');                    
        
    }

    function get_report_data()
    {
        $List = DB::table('histories')
                        ->limit(10)
                        ->get();
        return $List;
    }

    function pdf()
    {
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($this->convert_report_data_to_html());
        return $pdf->stream();
    }

    function convert_report_data_to_html()
    {
        $getReportData = $this->get_report_data();
        $output = '<h3 align="center">Report Generator</h3>
                     <table width="100%" style="border-collapse: collapse; border: 0px;">
                        <thead>
                            <tr style="backgroud-color:green">
                                    <th style="border: 1px solid; padding:6px;background-color: #4CAF50;" width="10%">NO</th>
                                    <th style="border: 1px solid; padding:12px;background-color: #4CAF50;" width="20%">Full Name</th>
                                    <th style="border: 1px solid; padding:6px;background-color: #4CAF50;" width="10%">Patient ID</th>
                                    <th style="border: 1px solid; padding:12px;background-color: #4CAF50;" width="20%">History</th>
                                    <th style="border: 1px solid; padding:12px;background-color: #4CAF50;" width="20%">Diagnosis</th>
                                    <th style="border: 1px solid; padding:12px;background-color: #4CAF50;" width="20%">Result</th>
                                    <th style="border: 1px solid; padding:12px;background-color: #4CAF50;" width="20%">Treated data</th>
                            </tr>
                        </thead>
                        <tbody>';
        foreach ($getReportData as $key => $value) 
        {
            $fullName = Reception::find($value->id);
            $fullName = $fullName->fullName;
            $No = ++$key;
            $output .='<tr >
                            <td style="border: 1px solid; padding:6px;">'.$No.'</td>
                            <td style="border: 1px solid; padding:12px;">'.$fullName.'</td>
                            <td style="border: 1px solid; padding:6px;">'.$value->id.'</td>
                            <td style="border: 1px solid; padding:12px;">'.$value->history.'</td>
                            <td style="border: 1px solid; padding:12px;">'.$value->dx.'</td>
                            <td style="border: 1px solid; padding:12px;">'.$value->result.'</td>
                            <td style="border: 1px solid; padding:12px;">'.$value->date.'</td>
                        </tr>';
        }
        $output .= '</tbody></table>';
        return $output;
    }

    
    // public function orders()
    // {
    //     return view('admin.orders')->with('lab_success','');
    // }



    public function labPostData(Request $request)
    {
        // $this->validate($request,[
        //         'labName' => 'required|alpha|unique:lab_finances',
        //         'price' => 'required|numeric'
        //     ]);
        // $labratory = new labFinance;
        // $labratory->labName = $request->labName;
        // $labratory->price = $request->price;
        // $labratory->save();
        // echo json_encode('hello');
        // $result[] = 'you are right'
        // echo json_encode($result);
        // return view('/');
        return 123;

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('home');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'f_name'    =>  'required|alpha',
            'l_name'     =>  'required|alpha',
            'email'     =>  'required|string|email|max:255|unique:employes',
            'role'     =>  'required'
        ]);
        $employer = new Employe([
            'f_name'    =>  $request->get('f_name'),
            'l_name'     =>  $request->get('l_name'),
            'email'     =>  $request->get('email'),
            'role'     =>  $request->get('role'),
        ]);
        $employer->save();
        return redirect('/admin')->with('success', 'Successfully registered');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return 123;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\altFinance;
use App\labFinance;
use App\medFinance;
use Gate;
use DB;
use Validator;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows("isAdmin")) {
            //abort(403,"Sorry, You can do this actions");
            return redirect('/');
        }
        $altList = altFinance::orderBy('updated_at','desc')->get();
        $labList = labFinance::orderBy('updated_at','desc')->get();
        $medList = medFinance::orderBy('updated_at','desc')->get();
        $medCatagory = DB::table('med_finances')
                                ->groupBy('catagory')
                                ->get();
        $labCatagory = DB::table('lab_finances')
                            ->groupBy('catagory')
                            ->get(); 
        $altCatagory = DB::table('alt_finances')
                            ->groupBy('catagory')
                            ->get();                                                             
        $data= array(
                    'altList' => $altList,
                    'labList' => $labList,
                    'medList' => $medList,
                    
                    );
         return view('admin.orders')->with(['data'=>$data,'medCatagory' => $medCatagory,'labCatagory'=>$labCatagory,'altCatagory'=>$altCatagory]);
        //echo $medCatagory[0]->catagory;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->input('type') == 'altrasound')
        {
            $validator = Validator::make($request->all(),[
                                    'altName' => 'required|string|unique:alt_finances,altName',
                                    'price' => 'required|numeric|min:0'
                                ]);
            $error_array = array();
            if($validator->fails())
            {
                foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                    $error_array[$field_name] = $messages;
                }
                echo json_encode($error_array);
            }
            else
            {
                $altName = new altFinance;
                $altName->altName = $request->input('altName');
                $altName->price = $request->input('price');
                $altName->save();
                return redirect('/orders/')->with('alt_success','Added successfully!!!');
            }

        }
        else
        {
            $this->validate($request,[
                                'labName' => 'required|string|unique:lab_finances,labName',
                                'price' =>'required|numeric|min:0'
                            ]);
            $labName = new labFinance;
            $labName->labName = $request->input('labName');
            $labName->price = $request->input('price');
            $labName->save();
            return redirect('/orders/')->with('success','Added successfully');
        }
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
        $lab = labFinance::find($id);
        return view('admin.orderEdit')->with('lab',$lab);
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
        $this->validate($request,[
                            'labName' => 'required|string',
                            'price' => 'required|numeric|min:0'
                        ]);
        $lab = labFinance::find($id);
        $lab->labName = $request->get('labName');
        $lab->price  = $request->get('price');
        $lab->save();
        return redirect('/orders')->with('success','Updated successfully!!!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $lab = labFinance::find($id);
        $lab->delete();
        return redirect('/orders')->with('success','Deleted successfully!!!');
    }
}

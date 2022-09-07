<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reception;
use DataTables;
use Gate;
use DB;
use App\StateCity;


class ReceptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Gate::allows('isReception'))
        {
            return redirect('/');
        }
        $stateList = DB::table('state_cities')
                        ->groupBy('state')
                        ->get();

         //$stateList = StateCity::distinct()->get(['state']);
         return view('reception.newReg')->with('state',$stateList);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetch_patient()
    {
        return view('reception.newReg');
    }

    function getdata()
    {
     $receptions = Reception::select('fullName', 'type');
     return DataTables::of($receptions)->make(true);
    }


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
        $this->validate($request,[
            'fullName' =>'required',
            'type' => 'required',
            'phone' => 'required',
            'opd_num' => 'required',
            'reagion' => 'required',
            'subcity' => 'required',
            'age' => 'required'
        ]);

        $patient = new reception([
            'fullName' => $request->get('fullName'),
            'type' => $request->get('type'),
            'phone' => $request->get('phone'),
            'opd_num' => $request->get('opd_num'),
            'age' => $request->get('age'),
            'gender' =>$request->get('gender'),
            'reagion' =>$request->get('reagion'),
            'subcity' =>$request->get('subcity')
        ]);

        $patient->save();
        return redirect()->route('reception.index')->with('success','Successfully registered');

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
        //
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\newPost;
use App\linkCounter;
use DB;
use DataTables;
class LinkCounterController extends Controller
{
    //
    public function index(Request $request)
    {
    	$posts = newPost::orderBy('id','desc')->paginate(3);
    	$arrayName = array('all' => $posts );
    	return view('linkCounter/index')->with($arrayName);
    }
    function Electronics()
    {
    	$posts = newPost::where('catagory','Electronics')->orderBy('id','asc')->paginate(3);
    	$arrayName = array('all' => $posts );
    	return view('linkCounter/electronics')->with($arrayName);
    }
    function Machinery()
    {
    	$posts = newPost::where('catagory','Machinery')->orderBy('id','desc')->paginate(3);
    	$arrayName = array('all' => $posts );
    	return view('linkCounter/machinery')->with($arrayName);
    }
    function Furniture()
    {
    	$posts = newPost::where('catagory','Furniture')->orderBy('id','desc')->paginate(3);
    	$arrayName = array('all' => $posts );
    	return view('linkCounter/Furniture')->with($arrayName);
    }
    function House()
    {
    	$posts = newPost::where('catagory','House')->orderBy('id','desc')->paginate(3);
    	$arrayName = array('all' => $posts );
    	return view('linkCounter/House')->with($arrayName);
    }
    function cheepest(Request $request)
    {
    	$type = $request->get('type');
    	if( $type == 'All')
    	{
    		$posts = newPost::orderBy('cost','asc')->paginate(3);
    		$arrayName = array('all' => $posts );
    		return view('linkCounter/index')->with($arrayName);
    	}
    	else
    	{
    		$posts = newPost::where('catagory',$type)->orderBy('cost','asc')->paginate(3);
    		$arrayName = array('all' => $posts );
    		return view('linkCounter/'.$type)->with($arrayName);
    	}
    	
    }
    function expensive(Request $request)
    {
    	$type = $request->get('type');
    	if( $type == 'All')
    	{
    		$posts = newPost::orderBy('cost','desc')->paginate(3);
    		$arrayName = array('all' => $posts );
    		return view('linkCounter/index')->with($arrayName);
    	}
    	else
    	{
    		$posts = newPost::where('catagory',$type)->orderBy('cost','desc')->paginate(3);
    		$arrayName = array('all' => $posts );
    		return view('linkCounter/'.$type)->with($arrayName);
    	}
    	
    }
    function dashboard()
    {
        $linkTotal = DB::table('link_counters')
        ->where('type','link')
        ->select(DB::raw('SUM(noClick) as total_link_click'))
        ->get();
        $btnTotal = DB::table('link_counters')
                    ->where('type','btn')
                    ->select(DB::raw('SUM(noClick) as total_btn_click'))
                    ->get();
        $arrayName = array('link_total' => $linkTotal,'btn_total'=>$btnTotal);
    	return view('linkCounter/dashboard')->with($arrayName);
    }

    function linkFetchData()
    {
            $linkList = DB::table('link_counters')
                            ->select('id', 'name','noClick')
                            ->where('type','=','link')
                            ->get();
             return DataTables::of($linkList)
                    ->make(true);
    }
    function btnFetchData()
    {
            $btnList = DB::table('link_counters')
                            ->select('id', 'name','noClick')
                            ->where('type','=','btn')
                            ->get();
             return DataTables::of($btnList)
                    ->make(true);
    }
    function pathFetchData()
    {
            $btnList = DB::table('link_counters')
                            ->select('id', 'name','noClick')
                            ->where('type','=','path')
                            ->get();
             return DataTables::of($btnList)
                    ->make(true);
    }
    function totalAnalytic()
    {
        $btnList = DB::table('link_counters')
                    ->where('type','btn')
                    ->select(DB::raw('SUM(noClick) as total_btn_click'))
                    ->get();
        $linkList = DB::table('link_counters')
                    ->where('type','link')
                    ->select(DB::raw('SUM(noClick) as total_link_click'))
                    ->get();
        $arrayName = array('linkTotal' => $linkList[0]->total_link_click,'btnTotal' => $btnList[0]->total_btn_click );                    
        echo json_encode($arrayName);
    }
    function newPost()
    {
    	return view('linkCounter/newPost');
    }
    function newPostSubmit(Request $request)
    {
    	$newPost = new newPost;
    	$newPost->name = $request->get('name');
    	$newPost->brand = $request->get('brand');
    	$newPost->catagory = $request->get('catagory');
    	$newPost->cost = $request->get('cost');
    	$newPost->status = $request->get('status');
    	$newPost->description = $request->get('description');
    	$newPost->phone = $request->get('phone');
    	$newPost->address = $request->get('address');
    	$image = $request->file('select_file');
    	if($image == '')
    		return back()->with('success','<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">x</button><strong>There is no image inserted<strong></div>');
    	$new_name = rand() . '.' . $image->getClientOriginalExtension();
    	$image->move(public_path("images"),$new_name);
    	$newPost->image = $new_name;
    	
    	if($newPost->save())
    	{
    		return back()->with('success','<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">x</button><strong>Image uploaded successfully<strong></div>');
    	}
    	else															
    	 	return back()->with('success','<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">x</button><strong>Error has been ocurred<strong></div>');
    }


    function clickCount(Request $request)
    {
    	$linkCheck = DB::table('link_counters')->where('name',$request->get('realLink'))->get();
    	$linkPathCheck = DB::table('link_counters')->where('name',$request->get('id'))->get();
    	if(!count($linkCheck))
		{
			$addNew =new linkCounter;
			$addNew->name = $request->get('realLink');
			$addNew->type = $request->get('type');
			$addNew->noClick = 1;
			$addNew->save();
			echo 'new added';
		}
		else
		{
			DB::table('link_counters')->where('name',$request->get('realLink'))->increment('noClick');
			echo 'updated';
		}
		if(!count($linkPathCheck))
		{

			$addNew =new linkCounter;
			$addNew->name = $request->get('id');
			$addNew->type = 'path';
			$addNew->noClick = 1;
			$addNew->save();
		}
		else
		{
			DB::table('link_counters')->where('name',$request->get('id'))->increment('noClick');
		}
    }
}

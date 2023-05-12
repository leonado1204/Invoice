<?php

namespace App\Http\Controllers;

use App\Customers;
use App\Invoice;
use Illuminate\Http\Request;

use App\Http\Requests\CustomersFormRequest;
use Session;

class CustomersController extends Controller
{
	function __construct()
	{
	    $this->middleware('auth');
	}
	
	public function index()
	{
		return view('customers.index');
	}
	
	public function create(Request $request)
	{
		
	}
	
	public function store(Request $request)
	{
		if($request->make == ""){
		Customers::create([
			'client' => $request->client,
			'client_address' => $request->client_address,
			'client_poskod'  => $request->client_poskod,
			'client_telephone' => $request->client_telephone,
			'client_email' => $request->client_email,
			]);
	
		// message to confirm storing data
		Session::flash('flash_message', 'Data successfully added!');
	
		// redirect back to original route
		return redirect()->back();

		}elseif($request->make != ""){
		Invoice::create([
			'ClientId' => $request->clientId,
			'make' => $request->make,
			'model'  => $request->model,
			'weight' => $request->weight,
			'engNum' => $request->engnum,
			'colour'  => $request->colour,
			'bodyStyle' => $request->bodystyle,
			'exported' => $request->exported,
			'gearBox'  => $request->gearbox,
			'ModelSetupDate' => $request->modeldate,
			'firRegis' => $request->firregis,
			]);
	
		// message to confirm storing data
		return redirect()->back();
		}
	//
	}

	public function storeInvoice(Request $request)
	{
		Invoice::create([
			'clientId' => $request->ClientId,
			'RegNumber' => $request->regNum,
			'make' => $request->make,
			'model'  => $request->model,
			'weight' => $request->weight,
			'engNum' => $request->engnum,
			'colour'  => $request->colour,
			'bodyStyle' => $request->bodystyle,
			'exported' => $request->exported,
			'gearBox'  => $request->gearbox,
			'ModelSetupDate' => $request->modeldate,
			'firRegis' => $request->firregis,
			]);
	
		// message to confirm storing data
		Session::flash('flash_message', 'Data successfully added!');
	
		// redirect back to original route
		return redirect()->back();

	//
	}
	
	
	public function show(Customers $customers)
	{
	//
	}
	
	public function edit(Customers $customers)
	{
		return view('customers.edit', compact(['customers']));
	}

	// public function edit2(Customers $customer)
	// {
		
	// 	return "ok";
	// 	// return view('sales.create', compact(['customers']));
	// }
	
	public function update(CustomersFormRequest $request, Customers $customers)
	{
		
		echo "ok";

		// $duit = Customers::find($customers->id)
		// 		->update(request([
		// 			'client', 'client_email', 'client_address', 'address_line2', 'city', 'state_province', 'client_poskod', 'client_telephone', 'client_mobile', 'updated_at',
		// 		]));
		// // $customers->touch();
		// // info when update success
		// Session::flash('flash_message', 'Data successfully edited!');
		// return redirect(route('customers.index'));
	}
	
	public function destroy(Customers $customers)
	{
		//
		Customers::destroy($customers->id);
		return redirect(route('customers.index'));

	}

	public function search(Request $request)
	{
		$valid = TRUE;
		$cust = Customers::where('client', $request->client)->count();
		$cust_email = Customers::where('client_email', $request->client_email)->count();
		$cust_phone = Customers::where('client_phone', $request->client_phone)->count();
		// dd($cust);

		if ($cust == 1) 
		{
			$valid = FALSE;
		}
		else 
		{
			if ($cust_phone == 1)
			{
				$valid = FALSE;
			}
			else
			{
				if ($cust_email == 1)
				{
					$valid = FALSE;
				} else {
					$valid = TRUE;
				}
			}
		}

		return response()->json(['valid' => $valid]);
	}
}
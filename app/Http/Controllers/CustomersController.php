<?php

namespace App\Http\Controllers;

use App\Customers;
use App\Invoice;
use App\Sale;
use App\Sales;
use App\SaleDes;
use App\SalesCustomers;
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
        dd($request);
		
	}
	
	public function store(Request $request)
	{
		if($request->clientname == ""){
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
            'RegNumber' => $request->regNum,
			]);
	
		// message to confirm storing data
		Session::flash('flash_message', 'Data successfully added!');
	
		// redirect back to original route
		return redirect()->back();

		}elseif($request->clientname != ""){

            $user = new Customers();

            $user->client = $request->clientname;
            $user->client_address = $request->clientadd;
            $user->client_poskod = $request->clientzip;
            $user->client_telephone = $request->clienttel;
            $user->client_email = $request->clientemail;
            $user->client_mobile = $request->clientmob;
            $user->address_line2 = $request->clientaddd;
            $user->city = $request->clientcity;
            $user->state_province = $request->clientstate;
            $user->country = $request->clientcountry;
            $user->save();

            $invoice = new Invoice();
			$invoice->ClientId = $user->id;
			$invoice->make = $request->make;
			$invoice->model = $request->model;
			$invoice->weight = $request->weight;
			$invoice->engNum = $request->engNum;
			$invoice->colour = $request->colour;
			$invoice->bodyStyle = $request->bodyStyle;
			$invoice->exported = $request->exported;
			$invoice->gearBox = $request->gearBox;
			$invoice->ModelSetupDate = $request->modelSetupDate;
			$invoice->firRegis = $request->firRegis;
            $invoice->RegNumber = $request->regNum;
            $invoice->price = $request->vehicleprice;
            $invoice->quantity = $request->quantity;
            $invoice->warranty = $request->warranty;

            $invoice->save();

            $salen = new Sale();
            $salen->id = $invoice->id;
            $salen->invoice_num = $invoice->id;
            $salen->user_id = auth()->user()->id;
            $salen->customer_id = $invoice->id;

            $salen->save();

            $salesn = new Sales();
            $salesn->id = $invoice->id;
            $salesn->id_user = auth()->user()->id;
            $salesn->date_sale = $salen->created_at;
            $salesn->registration_num = "0";
            $salesn->total_price = "0";
            $salesn->warranty = "0";
            $salesn->delivery_fee = "0";
            $salesn->custom_field = "0";
            $salesn->discount = "0";
            $salesn->part_exchange = "0";
            $salesn->deposit_paid = "0";
            $salesn->finance_paid = "0";

            $salesn->save();

            $salenDes = new SaleDes();
            $salenDes->sale_id = $invoice->id;
            $salenDes->description = $request->make." ".$request->model;
            $salenDes->quantity = 1;
            $salenDes->amount = 0;

            $salenDes->save();

            $salenCus = new SalesCustomers();
            $salenCus->id_sales = $salesn->id;
            $salenCus->id_customer = $user->id;
            $salenCus->id = $invoice->id;

            $salenCus->save();


            // message to confirm storing data
		return redirect("/sales/index");
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
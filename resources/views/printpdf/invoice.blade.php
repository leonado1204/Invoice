<?php

// load model
use App\Sale;
use App\Sales;
use App\SalesItems;
use App\SlipPostage;
use App\Customers;
use App\Product;
use App\ProductCategory;
use App\ProductImage;
use App\Payments;
use App\SlipNumbers;
use App\SalesTax;
use App\SalesCustomers;
use App\Preferences;
use App\Taxes;
use App\Banks;
use App\Invoice;

use Crabbly\Fpdf\Fpdf as Fpdf;
use Carbon\Carbon;


function my($string)
{
	$rt = Carbon::createFromFormat('Y-m-d', $string);
	return date('d F Y', mktime(0, 0, 0, $rt->month, $rt->day, $rt->year));
}


// load image
function base64ToImage($base64_string, $output_file)
{
	$ext = explode('/', $output_file);
	$filename = storage_path().'/uploads/pdfimages/'.mt_rand().'.'.$ext[1];
	file_put_contents($filename, base64_decode($base64_string));
	return $filename;
}

// echo Request::segment(2);

class PDF extends Fpdf
{
	// Page header
	function Header()
	{
		// invoice number
		$lo1 = Preferences::find(1);
		// Logo
		$this->Image(base64ToImage($lo1->company_logo_image, $lo1->company_logo_mime),10,7,30);
		// Arial bold 15
		$this->SetLeftMargin(60);
		$this->SetFont('Arial','B',11);
		$this->MultiCell(0, 5, $lo1->company_name, 0, 'L');
		$this->MultiCell(0, 5, $lo1->company_registration, 0, 'L');
		$this->MultiCell(0, 5, 'Tel : '.$lo1->company_fixed_line, 0, 'L');
		$this->MultiCell(0, 5, 'Mobile : '.$lo1->company_mobile, 0, 'L');
		$this->MultiCell(0, 5, 'Email : '.$lo1->company_email, 0, 'L');

		$this->SetFont('arial','B',20);
		$this->SetRightMargin(10);
		$this->SetY(10);
		$this->MultiCell(0, 5, 'Car Sales Invoice', 0, 'R');
		// Line break
		$this->Ln(5);
	}
	
	// Page footer
	function Footer()
	{
		// invoice number
		$lo2 = Preferences::find(1);

		// due to multicell setLeftMargin from the body of the page
		$this->SetLeftMargin(10);
		$this->SetTextColor(128);
		// Position at 3.0 cm from bottom
		$this->SetY(-23);
		$this->SetFont('arial','I',6);
		$this->Cell(0, 5, $lo2->company_address.' '.$lo2->company_postcode, 0, 1, 'C');
		// Arial italic 5
		$this->SetFont('Arial','I',5);
		// Page number
		$this->Cell(0,5,'Page '.$this->PageNo().'/{nb}',0,1,'C');
	}
}

	// dd($sales);

// invoice number
$lo = Preferences::find(1);



// Instanciation of inherited class
$pdf = new PDF('P','mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetTitle('Invoice ');

// echo $pdf->GetPageWidth();		// 210.00155555556
// echo $pdf->GetPageHeight();		// 297.00008333333

$pdf->SetFont('Arial','B',15);
$pdf->SetTextColor(145, 0, 181);
$pdf->SetFillColor(200,220,255);
$pdf->Line(0, 45, 250, 45);
// $pdf->Cell(0, 7, 'Invoice Number : '.$sales->id, 1, 1, 'C', true);

$pdf->Ln(10);

// reset font
$pdf->SetLeftMargin(10);
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(0, 0, 0);
// $pdf->SetFillColor(200,220,255);

// $pdf->Cell(95, 5, 'test', 0, 0, 'C');
// $pdf->Cell(95, 5, 'Invoice to : ', 0, 1, 'C');
$pdf->Ln(5);


$customer = SalesCustomers::where(['id_sales' => $sales->id])->first();
$lo2 = Customers::where(['id' => $customer->id_customer])->first();

// echo($lo2->client_address); exit();
// customer section
$pdf->SetRightMargin(105);
$pdf->SetFont('Arial','B',10);
$pdf->SetY(47);
$pdf->MultiCell(0, 5, 'Sold to : ', 0, 'L');
$pdf->SetFont('Arial','B',8);
$pdf->MultiCell(0, 5, $lo2->client, 0, 'L');
$pdf->MultiCell(0, 5, $lo2->client_address, 0, 'L');
$pdf->MultiCell(0, 5, ' ', 0, 'L');

$pdf->SetFont('Arial','B',10);
$pdf->MultiCell(0, 5, 'Delivery Address :', 0, 'L');
$pdf->SetFont('Arial','B',8);
$pdf->MultiCell(0, 5, $lo2->client, 0, 'L');
$pdf->MultiCell(0, 5, $lo2->client_address, 0, 'L');


$sacust = SalesCustomers::where(['id_sales' => $sales->id])->get();
foreach ($sacust as $key) {
	$cust = Customers::findOrFail($key->id_customer);
}




$pdf->SetFont('Arial','B',10);
// tracking number column
$pdf->SetRightMargin(10);
$pdf->SetLeftMargin(100);
$pdf->SetY(47);
$pdf->MultiCell(0, 5, 'Invoice Number : ', 0, 'L');
$pdf->MultiCell(0, 5, 'Invoice Date : ', 0, 'L');
$pdf->MultiCell(0, 5, 'Vehicle Reg : ', 0, 'L');
$pdf->MultiCell(0, 5, 'Make : ', 0, 'L');
$pdf->MultiCell(0, 5, 'Model : ', 0, 'L');
$pdf->MultiCell(0, 5, 'Color : ', 0, 'L');
$pdf->MultiCell(0, 5, 'Chassis Number : ', 0, 'L');
$pdf->MultiCell(0, 5, 'Engin Number : ', 0, 'L');
$pdf->MultiCell(0, 5, 'Date of Registration : ', 0, 'L');
$pdf->MultiCell(0, 5, 'Current Mileage : ', 0, 'L');
$pdf->MultiCell(0, 5, 'MOT Included : ', 0, 'L');
$pdf->MultiCell(0, 5, 'TAX Included : ', 0, 'L');

$vehicle = Invoice::where('ClientId', $lo2->id)->get();
foreach($vehicle as $vehicle){
$pdf->SetFont('Arial','B',8);
// tracking number list
$pdf->SetRightMargin(10);
$pdf->SetLeftMargin(160);
$pdf->SetY(47);
$pdf->MultiCell(0, 5, $sales->id, 0, 'L');
$pdf->MultiCell(0, 5, $sales->date_sale, 0, 'L');
$pdf->MultiCell(0, 5, $vehicle->RegNumber, 0, 'L');
$pdf->MultiCell(0, 5, $vehicle->make, 0, 'L');
$pdf->MultiCell(0, 5, $vehicle->model, 0, 'L');
$pdf->MultiCell(0, 5, $vehicle->colour, 0, 'L');
$pdf->MultiCell(0, 5, 'Wv32456Y78Y789784', 0, 'L');
$pdf->MultiCell(0, 5, $vehicle->engNum, 0, 'L');
$pdf->MultiCell(0, 5, $vehicle->firRegis, 0, 'L');
$pdf->MultiCell(0, 5, '102000', 0, 'L');
$pdf->MultiCell(0, 5, '1 Month(s)', 0, 'L');
$pdf->MultiCell(0, 5, '3 Month(s)', 0, 'L');
}
$pdf->Ln(10);


$pdf->SetFont('Arial','B',15);
$pdf->SetTextColor(145, 0, 181);
$pdf->SetFillColor(200,220,255);
$pdf->Line(0, 115, 250, 115);
// $pdf->Cell(0, 7, 'Invoice Number : '.$sales->id, 1, 1, 'C', true);
$pdf->Ln(1);




// // reset margin
// $pdf->SetX(10);
// $pdf->SetRightMargin(10);
// $pdf->SetLeftMargin(10);

// // reset font
// $pdf->SetFont('Arial','B',10);
// $pdf->SetTextColor(0, 0, 0);
// $pdf->Ln(5);

// $lipay = Payments::where(['id_sales' => $sales->id])->get();
// $py = 0;

// if ($lipay->count() > 0) {

// 	// header
// 	$pdf->SetFont('Arial', 'B', 8);
// 	$pdf->Cell(130, 7, 'Description', 1, 0, 'C');
// 	$pdf->Cell(30, 7, 'Quantity', 1, 0, 'C');
// 	$pdf->Cell(30, 7, 'Amount', 1, 1, 'C');
	
// 	// list of payment
// 	$pdf->SetFont('Arial','',8);
	
// 	$pdf->Cell(130, 7, 'Audi Q7 TDI Sport as Described Above', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '1', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '£'.$sales->total_price, 1, 1, 'C');
	
// 	$pdf->Cell(130, 7, 'VAT Qualifying Vehicle', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '@20%', 1, 0, 'C');
// 	$pdf->Cell(30, 7, 'Amount/1.20 = £'.$sales->total_price/1.2, 1, 1, 'C');
	
// 	$pdf->Cell(130, 7, 'Warranty', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '3 Months', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '£'.$sales->warranty, 1, 1, 'C');
	
// 	$pdf->Cell(130, 7, 'Delivery Fee, Delivery Address as Above', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '1', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '£'.$sales->delivery_fee, 1, 1, 'C');
	
// 	$pdf->Cell(130, 7, 'Custom Field', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '1', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '£'.$sales->custom_field, 1, 1, 'C');
	
// 	$pdf->Cell(130, 7, 'Discount', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '1', 1, 0, 'C');
// 	if($sales->discount > 0) {
// 		$pdf->Cell(30, 7, '£'.$sales->discount, 1, 1, 'C');
// 	}else{
// 		$pdf->Cell(30, 7, '-£'.abs($sales->discount), 1, 1, 'C');
// 	}
	
// 	$pdf->Cell(130, 7, 'Part Exchange BMW 325 SE, Reg LE12RED, Red, 312500 Miles', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '1', 1, 0, 'C');
// 	if($sales->part_exchange > 0) {
// 		$pdf->Cell(30, 7, '£'.$sales->part_exchange, 1, 1, 'C');
// 	}else{
// 		$pdf->Cell(30, 7, '-£'.abs($sales->part_exchange), 1, 1, 'C');
// 	}
	
// 	$pdf->Cell(130, 7, 'Deposit Paid', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '1', 1, 0, 'C');
// 	if($sales->deposit_paid > 0) {
// 		$pdf->Cell(30, 7, '£'.$sales->deposit_paid, 1, 1, 'C');
// 	}else{
// 		$pdf->Cell(30, 7, '-£'.abs($sales->deposit_paid), 1, 1, 'C');
// 	}
	
// 	$pdf->Cell(130, 7, 'Finance Paid', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '1', 1, 0, 'C');
// 	if($sales->finance_paid > 0) {
// 		$pdf->Cell(30, 7, '£'.$sales->finance_paid, 1, 1, 'C');
// 	}else{
// 		$pdf->Cell(30, 7, '-£'.abs($sales->finance_paid), 1, 1, 'C');
// 	}
	
// 	$total = $sales->total_price + 

// 	// footer
// 	$pdf->SetFont('Arial','B',10);
// 	$pdf->Cell(130, 7, '', 0, 0, 'C');
// 	$pdf->Cell(30, 7, 'Total', 1, 0, 'C');
// 	$pdf->Cell(30, 7, '', 1, 1, 'C');
	
// 	$pdf->Cell(130, 7, '', 0, 0, 'C');
// 	$pdf->Cell(30, 7, 'Paid', 1, 0, 'C');
// 	$pdf->Cell(30, 7, 'Card', 1, 1, 'C');
// } else {
// 	$pdf->SetFont('Arial','',8);
// 	$pdf->Cell(0, 7, 'Sorry, no payment yet.', 0, 1, 'C');
// }
// $pdf->Ln(5);



// for ($i=0; $i < 100; $i++) { 
// 	$pdf->Cell(0,5,'asd', 1,1,'C');
// }


$pdf->SetFont('Arial','',6);
$pdf->SetY(-31);
$pdf->Cell(0, 4, 'Invoice was created on a computer and is valid without the signature and seal.', 0,1,'L');
$pdf->Cell(0, 4, 'NOTICE: A finance charge of 1.5% will be made on unpaid balances after 30 days from the date of the invoice.', 0,1,'L');

$filename = 'Invoice for '.$cust->client.'.pdf';

$pdf->Output('I', $filename);		// <-- kalau nak bukak secara direct saja
// $pdf->Output('D');			// <-- semata mata 100% download
// $pdf->Output('F', storage_path().'/uploads/pdf/'.$filename);			// <-- send through email

?>
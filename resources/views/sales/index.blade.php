@extends('layout.master')

@section('content')
	@include('layout.errorform')
	@include('layout.info')

	<div class="col-lg-12" style="margin-top: 100px;">
		<div class="panel panel-default">
			<h3 class="nk-block-title page-title" style="margin-left: 4%; margin-top:20px;">Invoice Panel</h3>
			<div class="panel-body">
			@if(auth()->user()->id_group == 1)
			@else
			<p class="text-right"><a href="{!! route('sales.create') !!}" class="btn btn-info">New Invoice</a></p>
			@endif
				<div class="col-lg-12 table-responsive" id="load-products">
					<div class="card-inner">
						<?php
							use Carbon\Carbon;

							function my($string) {
								$rt = Carbon::createFromFormat('Y-m-d', $string);
								return date('d F Y', mktime(0, 0, 0, $rt->month, $rt->day, $rt->year));
							}
						?>

						<table id="example" class="table table-hover">
							<thead>
								@if(auth()->user()->id_group == 1)
								<th>User</th>
								@endif
								<!-- <th>editing</th> -->
								<th>Invoice Number</th>
								<th>Invoice Date</th>
								<th>Tracking Number</th>
								<th>Total Commission</th>
								<th>Total Invoice</th>
								<th>Total Payment</th>
								<th>Status</th>
								<th>Action</th>
							</thead>
							<tbody>
								<?php
									if (auth()->user()->id_group == 1) {
										$inv = App\Sales::where(['deleted_at' => NULL])->get();
									} else {
										$inv = App\Sales::where(['deleted_at' => NULL, 'id_user' => auth()->user()->id])->get();
									}
								?>
								@foreach($inv as $in)
								<?php
									///////////////////////////////////////////////////////////////////////////////////////////////
									$ttax = App\SalesTax::where(['id_sales' => $in->id, 'deleted_at' => NULL])->get();
									$tcharge = 0;
									foreach ($ttax as $k) {
										$tcharge += App\Taxes::findOrFail($k->id_tax)->amount;
									}

									///////////////////////////////////////////////////////////////////////////////////////////////
									
									///////////////////////////////////////////////////////////////////////////////////////////////
									$tinv = App\SalesItems::where(['id_sales' => $in->id, 'deleted_at' => NULL])->get();
									$tamo = 0;
									foreach ($tinv as $tin) {
										// echo $tin->retail.'&nbsp;'.$tin->quantity.'<br />';
										$tamo += $tin->retail * $tin->quantity;
										// echo $tamo.' total amount<br />';
									}
									//total amount
									$tamo = $tamo + ( $tamo * ($tcharge / 100) );


									$tcomm = 0;
									foreach ($tinv as $tinc) {
										// echo $tinc->commission.'&nbsp;'.$tinc->quantity.'<br />';
										$tcomm += $tinc->commission * $tinc->quantity;
										// echo $tcomm.' total amount<br />';
									}
									///////////////////////////////////////////////////////////////////////////////////////////////

									$pay = App\Payments::where(['id_sales' => $in->id, 'deleted_at' => NULL])->get();
									$paya = 0;
									foreach ($pay as $py) {
										$paya += $py->amount;
									}
									$re = $paya - $tamo;
								?>
								<tr class="<?=($re < 0)? 'danger' : '' ?>">
									@if(auth()->user()->id_group == 1)
									<td>{{ App\User::find($in->id_user) ? App\User::find($in->id_user)->name : '' }}</td>
									@endif
									<!-- <td>afaddfaf</td> -->
									<td>{!! $in->id !!}</td>
									<td>{!! my($in->date_sale) !!}</td>
									<!-- <td>{!! $in->no_tracking !!}</td> -->
									<td>
										<?php
											$slip = App\SlipNumbers::where(['id_sales' => $in->id])->get();
											foreach ( $slip as $imu ) {
												echo $imu->tracking_number.'<br />';
											}
										?>
									</td>
									<td>RM {!! number_format($tcomm,2) !!}</td>
									<td>RM {!! number_format($tamo, 2) !!}</td>
									<td>RM {!! number_format($paya, 2) !!}</td>
									<td><p class="btn <?php echo ($re < 0)? 'btn-danger' : 'btn-success' ?>"><?php echo ($re < 0) ? '<i class="fa fa-credit-card fa-lg" aria-hidden="true"></i>' : '<i class="fa fa-money fa-lg" aria-hidden="true"></i>' ?></p></td>
									<td>
										<!-- <a href="{!! route('sales.edit', $in->id) !!}" title="Edit"><i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i></a>
                                        &nbsp; -->
										<a href="{!! route('sales.destroy', $in->id) !!}" data-id="{!! $in->id !!}" data-token="{{ csrf_token() }}" id="delete_product_<?=$in->id ?>" title="Delete" class="delete_button">
											<i class="fa fa-trash fa-lg" aria-hidden="true"></i>
										</a>
										&nbsp;
										<a href="{!! route('printpdf.print', $in->id) !!}" target="_blank" title="Print PDF"><i class="fa fa-file-pdf-o fa-lg" aria-hidden="true"></i></a>
										@if(auth()->user()->id_group == 1)
										@else
											&nbsp;
											<a href="{!! route('emailpdf.send', $in->id) !!}" title="Send Email"><i class="fa fa-envelope-o fa-lg" aria-hidden="true"></i></a>
										@endif
										</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection


@section('jquery')

/////////////////////////////////////////////////////////////////////////////////////////

$.ajaxSetup({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
});

// ajax post delete row
	// readProducts(); /* it will load products when document loads */

	$(document).on('click', '.delete_button', function(e){
		var productId = $(this).data('id');
		SwalDelete(productId);
		e.preventDefault();
	});
	
	// function readProducts(){
	// 	$('#load-products').load('read.php');
	// }

	function SwalDelete(productId){
		swal.fire({
			title: 'Are you sure?',
			text: "It will be deleted permanently!",
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!',
			showLoaderOnConfirm: true,
			allowOutsideClick: false,

			preConfirm: function()                {
				return new Promise(function(resolve) {
				});
			},
		})
		.then((result) => {
			if(result.dismiss === swal.DismissReason.cancel) {
				swal.fire('Cancelled', 'Your data is safe', 'info');
			}
		});
	}

/////////////////////////////////////////////////////////////////////////////////////////
@endsection
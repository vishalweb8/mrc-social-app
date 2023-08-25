@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	Public Website Payments
    @can(config('perm.addPublicWebsitePayments'))
	    <a href="{{route('PublicWebsitepayments.create')}}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;Add Public Website Payments</a>
    @endcan
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
					<div id="subscriptionsplan_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					
						<div class="row"><div class="col-sm-12"><table class="table table-bordered table-striped dataTable no-footer" id="subscriptionsplan" role="grid" aria-describedby="subscriptionsplan_info">
							<thead>
								<tr role="row">
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 70px;" aria-label="Id: activate to sort column ascending">
										Id
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 84px;" aria-label="Name: activate to sort column ascending">
									Payment Id
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 203px;" aria-label="No of Month: activate to sort column ascending">
										Public Website Name
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 80px;" aria-label="Price: activate to sort column ascending">
									 Amount
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Payment Date
									</th>
									{{-- <th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									pay_trans_id
									</th> --}}
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Payment Status
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Payment Message
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 139px;" aria-label="Actions: activate to sort column ascending">
									Actions
									</th>
								</tr>
							</thead>
							<tbody>
					@foreach($publicWebsitePayments as $publicWebsitePaymentsGet)		


                          <tr role="row" class="odd">

                                <td>
                                    {{$publicWebsitePaymentsGet->id}}
                                </td>
                                <td>
                                    {{$publicWebsitePaymentsGet->pay_trans_id}}
                                </td>
                                <td>
                                    {{$publicWebsitePaymentsGet->publicWebsiteName->website_name ?? ''}}
                                </td> 
                                <td>
                                    {{$publicWebsitePaymentsGet->payment_amount}}
                                </td> 
                                <td>
                                    {{$publicWebsitePaymentsGet->payment_date}}
                                </td> 
                                <td>
                                    {{$publicWebsitePaymentsGet->payment_status}}
                                </td>
                                <td>
                                    {{$publicWebsitePaymentsGet->payment_message}}
                                </td>
                                
                                <td>
                                    @can(config('perm.editPublicWebsitePayments'))
                                    <a href="{{ url('admin/allpublicwebsitepayments/edit') }}/{{Crypt::encrypt($publicWebsitePaymentsGet->id)}}">
                                        <span data-toggle="tooltip" data-original-title="Edit" class="glyphicon glyphicon-edit"></span>

                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.deletePublicWebsitePayments'))
                                    <a onclick="return confirm('Are you sure you want to delete ?')" href="{{ url('admin/allpublicwebsitepayments/remove') }}/{{Crypt::encrypt($publicWebsitePaymentsGet->id)}}">
                                        <span data-toggle="tooltip" data-original-title="Delete" class="glyphicon glyphicon-remove"></span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    
                                </td>
                           </tr>
                           @endforeach
							</tbody>
							
				</table>
			</div>
		</div>
		
	</div>
				</div><!-- /.box-body -->
			</div>
			<!-- /.box -->
			
			</div><!-- /.col -->
			</div><!-- /.row -->
		</section>
		@stop
		@section('script')
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script><script type="text/javascript">
			$(document).ready(function() {
        $('#subscriptionsplan').DataTable({
            hideEmptyCols: true,
           "aaSorting": []
        });
    });
		</script>
		@stop
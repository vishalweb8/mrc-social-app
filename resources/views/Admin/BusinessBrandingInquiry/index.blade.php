@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

@stop
@section('content')
<section class="content-header">
	<h1>
	{{trans('labels.business_branding_inquiry')}}
    @can(config('perm.addAdvertise'))
	    <a href="{{ route('businessBrandingInquiry.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.business_branding_inquiry')}}</a>
    @endcan
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
					<div id="business_branding_inquiry_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-bordered table-striped dataTable no-footer" id="business_branding_inquiry" role="grid">
									<thead>
										<tr role="row">
											<th>
												Id
											</th>
											<th>
												Business
											</th>											
											<th>
												Business Name
											</th>											
											<th>
												Name
											</th>
											<th>
												Mobile Number
											</th>
											<th>
												City
											</th>
											<th>
												Type
											</th>
											<th>
												Created Date
											</th>
											<th>
												Status
											</th>
											<th>Actions</th>									
										</tr>
									</thead>
									<tbody>   
											
										@foreach($inquiries as $inquiry)		


										<tr role="row" class="odd">

											<td>
												{{$inquiry->id}}
											</td>
											<td>
												{{$inquiry->business->name ?? ''}}
											</td>
											<td>
												{{$inquiry->business_name}} 
											</td>
											<td>
												{{$inquiry->name}}                                
											</td>
											<td>
												{{$inquiry->mobile_number}}
											</td>
											<td>
												{{$inquiry->city}}
											</td>
											<td>
												{{$inquiry->type}}
											</td>
											<td>
												{{$inquiry->created_at->format("Y-m-d H:i")}} 
											</td>
											<td style="text-transform: capitalize;">
												{{$inquiry->status}} 
											</td>
											<td>
                                                @can(config('perm.editAdvertise'))
												<a href="{{ route('businessBrandingInquiry.edit',$inquiry->id) }}">
													<span data-toggle="tooltip" data-original-title="Edit" class="glyphicon glyphicon-edit"></span>

												</a>&nbsp;&nbsp;
                                                @endcan
                                                @can(config('perm.deleteAdvertise'))
												<a onclick="if(confirm('Are you sure you want to delete ?')) { event.preventDefault(); document.getElementById('business-inquiry-{{$inquiry->id}}').submit(); } else return false" href="#">
													<span data-toggle="tooltip" data-original-title="Delete" class="glyphicon glyphicon-remove"></span>
												</a>&nbsp;&nbsp;
												<form id="business-inquiry-{{$inquiry->id}}" action="{{ route('businessBrandingInquiry.destroy',$inquiry->id) }}" method="post" style="display: none;">
													@csrf
													@method('DELETE')
												</form>
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
			</div><!-- /.box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</section>
@stop
@section('script')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('#business_branding_inquiry').DataTable({
                hideEmptyCols: true,
				"aaSorting": [
                    [0, 'desc']
                ],
				columnDefs: [{ 'orderable': false, 'targets': 8 }]
			});
		});
	</script>
@stop
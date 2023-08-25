@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

@stop
@section('content')
<section class="content-header">
	<h1>
	{{trans('labels.business_branding')}}
	<a href="{{ route('businessBranding.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.business_branding')}}</a>
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
					<div id="business_branding_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-bordered table-striped dataTable no-footer" id="business_branding" role="grid" aria-describedby="business_branding_info">
									<thead>
										<tr role="row">
											<th class="sorting" tabindex="0" aria-controls="business_branding" rowspan="1" colspan="1" >
												Id
											</th>
											<th class="sorting" tabindex="0" aria-controls="business_branding" rowspan="1" colspan="1">
											Business Name
											</th>											
											<th class="sorting" tabindex="0" aria-controls="business_branding" rowspan="1" colspan="1">
											Views
											</th>											
											<th class="sorting" tabindex="0" aria-controls="business_branding" rowspan="1" colspan="1">
											Clicks
											</th>
											<th class="sorting" tabindex="0" aria-controls="business_branding" rowspan="1" colspan="1">Start Date
											</th>
											<th class="sorting" tabindex="0" aria-controls="business_branding" rowspan="1" colspan="1">
											End Date
											</th>
											<th class="sorting" tabindex="0" aria-controls="business_branding" rowspan="1" colspan="1">
											Created Date
											</th>
											<th class="sorting" tabindex="0" aria-controls="business_branding" rowspan="1" colspan="1">
											Status
											</th>
											<th>Actions</th>									
										</tr>
									</thead>
									<tbody>   
											
										@foreach($brandings as $branding)		


										<tr role="row" class="odd">

											<td>
												{{$branding->id}}
											</td>
											<td>
												{{$branding->business->name ?? ''}}
											</td>
											<td>
												{{$branding->views}} 
											</td>
											<td>
												{{$branding->clicks}}                                
											</td>
											<td>
												{{$branding->start_date}}
											</td>
											<td>
												{{$branding->end_date}}
											</td>
											<td>
												{{$branding->created_at->format("Y-m-d H:i")}} 
											</td>
											<td style="text-transform: capitalize;">
												{{$branding->status}} 
											</td>
											<td>
												<a href="{{ route('businessBranding.edit',$branding->id) }}">
													<span data-toggle="tooltip" data-original-title="Edit" class="glyphicon glyphicon-edit"></span>

												</a>&nbsp;&nbsp;
												<a onclick="if(confirm('Are you sure you want to delete ?')) { event.preventDefault(); document.getElementById('business-brand-{{$branding->id}}').submit(); } else return false" href="#">
													<span data-toggle="tooltip" data-original-title="Delete" class="glyphicon glyphicon-remove"></span>
												</a>&nbsp;&nbsp;
												<form id="business-brand-{{$branding->id}}" action="{{ route('businessBranding.destroy',$branding->id) }}" method="post" style="display: none;">
													@csrf
													@method('DELETE')
												</form>
												
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
			$('#business_branding').DataTable({
                hideEmptyCols: true,
				"aaSorting": [
                    [0, 'desc']
                ],
				columnDefs: [{ 'orderable': false, 'targets': 8 }]
			});
		});
	</script>
@stop
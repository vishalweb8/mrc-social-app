@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

@stop
@section('content')
<style>
.str-cap {
	text-transform: capitalize;
}
</style>
<section class="content-header">
	<h1>
	{{trans('labels.advisors')}}
    @can(config('perm.addAdvisors'))
	<a href="{{ route('advisor.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.advisor')}}</a>
    @endcan
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
					<div class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-bordered table-striped dataTable no-footer" id="advisor" >
									<thead>
										<tr role="row">
											<th>
												Id
											</th>
											<th>
												Name
											</th>
											<th>
												Email
											</th>
											<th>
												Mobile
											</th>
											<th>
												Position
											</th>
											<th>
												Descriptions
											</th>
											<th>
												Status
											</th>
											<th>
												Date
											</th>
											<th>
												Actions
											</th>				
										</tr>
									</thead>
									<tbody>   
											
										@foreach($advisors as $advisor)		


										<tr role="row" class="odd">

											<td>
												{{$advisor->id}}
											</td>
											<td>
												{{$advisor->name}}
											</td>
											<td>
												{{$advisor->email}} 
											</td>
											<td>
												{{$advisor->mobile_number}}                                
											</td>
											<td>
												{{$advisor->position}}
											</td>
											<td>
												{{$advisor->description}}
											</td>
											<td class="str-cap">
												{{$advisor->status}}
											</td>
											<td>
												{{$advisor->created_at->format("Y-m-d H:i")}}                                                                       
											</td>
											<td>
                                                @can(config('perm.editAdvisors'))
												<a href="{{ route('advisor.edit',$advisor->id) }}">
													<span data-toggle="tooltip" data-original-title="Edit" class="glyphicon glyphicon-edit"></span>

												</a>&nbsp;&nbsp;
                                                @endcan
                                                @can(config('perm.deleteAdvisors'))
												<a onclick="if(confirm('Are you sure you want to delete ?')) { event.preventDefault(); document.getElementById('advisor-{{$advisor->id}}').submit(); } else return false" href="#">
													<span data-toggle="tooltip" data-original-title="Delete" class="glyphicon glyphicon-remove"></span>
												</a>&nbsp;&nbsp;
												<form id="advisor-{{$advisor->id}}" action="{{ route('advisor.destroy',$advisor->id) }}" method="post" style="display: none;">
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
			$('#advisor').DataTable({
                hideEmptyCols: true,
				aaSorting: [],
				columnDefs: [{ 'orderable': false, 'targets': 8 }]
			});
		});
	</script>
@stop

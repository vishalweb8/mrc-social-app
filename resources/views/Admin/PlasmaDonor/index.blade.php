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
	Plasma Donors
	<a href="{{ route('plasmaDonor.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.plasma_donor')}}</a>
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
					<div id="plasma_donor_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-bordered table-striped dataTable no-footer" id="plasma_donor" role="grid" aria-describedby="plasma_donor_info">
									<thead>
										<tr role="row">
											<th>
												Id
											</th>
											<th>
												Name
											</th>
											<th>
												Mobile Number
											</th>
											<th>
												Blood Group
											</th>
                                            <th>
												City
											</th>
											<th>
												Covid start date
											</th>
											<th>
												Status
											</th>
											<th>
												Actions
											</th>				
										</tr>
									</thead>
									<tbody>   
											
										@foreach($donors as $donor)		


										<tr role="row" class="odd">

											<td>
												{{$donor->id}}
											</td>
											<td>
												{{$donor->name}}
											</td>
											<td>
												{{$donor->mobile_number}} 
											</td>
                                            <td>
												{{$donor->blood_group}} 
											</td>
                                            <td>
												{{$donor->city}} 
											</td>
											<td>
												{{$donor->covid_start_date}}                                
											</td>
											<td class="str-cap">
												{{$donor->status}}
											</td>
											<td>
												<a href="{{ route('plasmaDonor.edit',$donor->id) }}">
													<span data-toggle="tooltip" data-original-title="Edit" class="glyphicon glyphicon-edit"></span>

												</a>&nbsp;&nbsp;
												<a onclick="if(confirm('Are you sure you want to delete ?')) { event.preventDefault(); document.getElementById('plasma-donor-{{$donor->id}}').submit(); } else return false" href="#">
													<span data-toggle="tooltip" data-original-title="Delete" class="glyphicon glyphicon-remove"></span>
												</a>&nbsp;&nbsp;
												<form id="plasma-donor-{{$donor->id}}" action="{{ route('plasmaDonor.destroy',$donor->id) }}" method="post" style="display: none;">
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
			$('#plasma_donor').DataTable({
                hideEmptyCols: true,
				aaSorting: [],
				columnDefs: [{ 'orderable': false, 'targets': 7 }]
			});
		});
	</script>
@stop
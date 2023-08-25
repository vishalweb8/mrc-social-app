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
	Online Stores
    @can(config('perm.addOnlineStore'))
	<a href="{{ route('onlineStore.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} Online Store</a>
    @endcan
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
					<div id="online_store_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-bordered table-striped dataTable no-footer" id="online_store" role="grid" aria-describedby="online_store_info">
									<thead>
										<tr role="row">
											<th>
												Id
											</th>
											<th>
												Name
											</th>
											<th>
												Logo
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
											
										@foreach($stores as $store)		


										<tr role="row" class="odd">

											<td>
												{{$store->id}}
											</td>
											<td>
												{{$store->name}}
											</td>
											<td>
                                                @if(!empty($store->logo)) 
                                                    <img style="cursor: pointer;" data-toggle='modal' data-target='#store-logo{{$store->id}}' src="{{ $store->logo }}" width="50" height="50" class="img-circle"/>
                                                    <div class='modal modal-centered fade image_modal' id='store-logo{{$store->id}}' role='dialog' style='vertical-align: center;'>
                                                        <div class='modal-dialog modal-dialog-centered'>
                                                            <div class='modal-content' style="background-color:transparent;">
                                                                <div class='modal-body'>
                                                                <center>
                                                                    <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                                                    <img src="{{ $store->logo }}" style='width:100%; border-radius:5px;' />
                                                                <center>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <img src="{{ url('images/default.png') }}" width="50" height="50" class="img-circle"/>
                                                @endif                                
											</td>
											<td class="str-cap">
												{{ ($store->status == 1 ) ? 'Active' : 'Inactive' }}
											</td>
											<td>
                                            @can(config('perm.editOnlineStore'))
												<a href="{{ route('onlineStore.edit',$store->id) }}">
													<span data-toggle="tooltip" data-original-title="Edit" class="glyphicon glyphicon-edit"></span>

												</a>&nbsp;&nbsp;
                                            @endcan
                                            @can(config('perm.deleteOnlineStore'))
												<a onclick="if(confirm('Are you sure you want to delete ?')) { event.preventDefault(); document.getElementById('online-store-{{$store->id}}').submit(); } else return false" href="#">
													<span data-toggle="tooltip" data-original-title="Delete" class="glyphicon glyphicon-remove"></span>
												</a>&nbsp;&nbsp;
												<form id="online-store-{{$store->id}}" action="{{ route('onlineStore.destroy',$store->id) }}" method="post" style="display: none;">
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('#online_store').DataTable({
                hideEmptyCols: true,
				aaSorting: [],
				columnDefs: [{ 'orderable': false, 'targets': 4 }]
			});
		});
	</script>
@stop
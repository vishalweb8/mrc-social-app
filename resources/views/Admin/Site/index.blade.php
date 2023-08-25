@extends('Admin.Master')
@section('content')

<section class="content-header">
	<h1>
	{{trans('labels.sites')}}
    @can(config('perm.addSite'))
    <a href="{{ route('site.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.site')}}</a>
    @endcan
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
                <div class="box-header">
                    <div class="col-md-2">
                        <select class="form-control" id="assetType">
                            <option value='' selected>All Site Category</option>
                            @foreach($assetTypes as $assetType)
                                <option value='{{$assetType->id}}' >{{$assetType->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" id="visibility">
                            <option value='' selected>All Visibility</option>
                            <option value=false>Public</option>
                            <option value=true>Private</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" id="approval">
                            <option value='' selected>All Approval</option>
                            <option value=0>Pending</option>
                            <option value=1>Approved</option>
                            <option value=2>Rejected</option>
                        </select>
                    </div>
                </div>
				<div class="box-body table-responsive">                        
                    <table class="table table-bordered" id="sites-table" role="grid"></table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
    <form id="delete-site-form" action="#" method="post" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</section>
@stop
@section('script')	
	<script type="text/javascript">
		$(document).ready(function() {
            $('#sites-table').DataTable({
                hideEmptyCols: true,
                processing: true,
                serverSide: true,
                ajax: { 
                    url: "{{ route('site.index') }}",
                    data: function(data) {
                        data.asset_type_id = $("#assetType").val();
                        data.visibility = $("#visibility").val();
                        data.is_approved = $("#approval").val();
                    }
                },
                aaSorting: [[0, 'desc']],
                columns: [
                    {data: 'id', name: 'id', title: 'Id'},
                    {data: 'name', name: 'name', title: 'Name'},
                    {data: 'asset_type', name: 'assetType.name', title: 'Site Category'},
                    {data: 'visibility', name: 'visibility', title: 'Visibility'},
                    {data: 'created_by', name: 'createdBy.name', title: 'Created By'},
                    {data: 'created_at', name: 'created_at', title: 'Created At'},
                    {data: 'is_approved', name: 'is_approved', title: 'Approvel Status'},
                    //{data: 'approved_at', name: 'approved_at', title: 'Approved At'},
                    //{data: 'status', name: 'status', title: 'Status'},
                    {data: 'action', name: 'action', title: 'Action',orderable:false, searchable:false}
                ]
            });

            $(document).on("click",".approve-reject-site",function(e) {
                e.preventDefault();
                var status = $(this).data('status');
                if( status == 1) {
                    var isConfirm = confirm('Are you sure you want to approve?');
                } else {
                    var isConfirm = confirm('Are you sure you want to reject?');
                }
                if(isConfirm) {
                    var url = $(this).data('url');
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {is_approved:status, _token:'{{csrf_token()}}'},
                        success: function( response ) {
                            if(response.status) {
                                showSuccessMessage(response.message);
                            } else {
                                showErrorMessage(response.message);
                            }
                            $('#sites-table').DataTable().ajax.reload();                            
                        }
                    });
                }
            });
            
            $(document).on("click",".delete-site",function(e) {
                e.preventDefault();
                var isConfirm = confirm('Are you sure? you want to delete this.');
                if(isConfirm) {
                    $("#delete-site-form").attr('action',$(this).data('url'));
                    $("#delete-site-form").submit();
                }            
            });

            $(document).on("change","#assetType,#visibility,#approval",function(e) {
                $('#sites-table').DataTable().ajax.reload();         
            });
		});
	</script>
@stop
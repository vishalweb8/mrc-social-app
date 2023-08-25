@extends('Admin.Master')
@section('content')

<section class="content-header">
	<h1>
	{{trans('labels.reasons')}}
    @can(config('perm.addReason'))
	    <a href="{{ route('reason.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.reason')}}</a>
    @endcan
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
                    <table class="table table-bordered" id="reason" role="grid"></table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
    <form id="delete-reason-form" action="#" method="post" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</section>
@stop
@section('script')	
	<script type="text/javascript">
		$(document).ready(function() {
            $('#reason').DataTable( {
                hideEmptyCols: true,
                processing: true,
                serverSide: true,
                ajax: { 
                    url: "{{ route('reason.index') }}"
                },
                aaSorting: [[0, 'desc']],
                columns: [
                    {data: 'id', name: 'id', title: 'Id'},
                    {data: 'entity_type', name: 'assetType.name', title: 'Asset Type'},
                    {data: 'reason', name: 'reason', title: 'Reason'},
                    {data: 'action', title: 'Action', orderable:false,searchable:false}
                ]
            });
            
            $(document).on("click",".delete-reason",function(e) {
                e.preventDefault();
                var isConfirm = confirm('Are you sure? you want to delete this.');
                if(isConfirm) {
                    $("#delete-reason-form").attr('action',$(this).data('url'));
                    $("#delete-reason-form").submit();
                }            
            });
            $('#reason_filter input').unbind();
            $("#reason_filter input").on('change',function(e) {
                $('#reason').DataTable().search($(this).val()).draw();
            });
		});
	</script>
@stop
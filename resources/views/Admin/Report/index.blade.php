@extends('Admin.Master')
@section('content')

<section class="content-header">
	<h1>
	{{trans('labels.reports')}}
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
                <div class="box-header">
                    <div class="col-md-2">
                        <select class="form-control" name="assetType" id="assetType">
                            <option value='' selected>All {{trans('labels.asset_type')}}</option>
                            @foreach($assetTypes as $assetType)
                                <option value='{{$assetType->asset_type_id}}' >{{$assetType->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                    <input type="text" class="form-control" id="reportedDate"  placeholder="Reported At">
                    </div>
                </div>
				<div class="box-body table-responsive">                        
                    <table class="table table-bordered" id="reports-table" role="grid"></table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
    <form id="delete-report-form" action="#" method="post" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</section>
@stop
@section('script')	
	<script type="text/javascript">
		$(document).ready(function() {
            $('#reports-table').DataTable({
                hideEmptyCols: true,
                processing: true,
                serverSide: true,
                ajax: { 
                    url: "{{ route('report.index') }}",
                    data: function(data) {
                        data.asset_type_id = $("#assetType").val();
                        data.created_at = $("#reportedDate").val();
                    }
                },
                aaSorting: [[0, 'desc']],
                columns: [
                    {data: 'id', name: 'id', title: 'Id'},
                    {data: 'report_by', name: 'reportBy.name', title: 'Report By'},
                    {data: 'comment', name: 'comment', title: 'Comment', orderable:false},
                    {data: 'created_at', name: 'created_at', title: 'Reported At'},
                    {data: 'asset_type', name: 'assetType.name', title: 'Asset Type'},
                    {data: 'action', name: 'action', title: 'Action',orderable:false, searchable:false}
                ]
            });
            
            $(document).on("click",".delete-report",function(e) {
                e.preventDefault();
                var isConfirm = confirm('Are you sure? you want to delete this.');
                if(isConfirm) {
                    $("#delete-report-form").attr('action',$(this).data('url'));
                    $("#delete-report-form").submit();
                }            
            });

            $(document).on("change","#assetType",function(e) {
                $('#reports-table').DataTable().ajax.reload();         
            });

            $(document).on("change","#reportedDate",function(e) {
                $('#reports-table').DataTable().ajax.reload();         
            });

            var FromEndDate = new Date();
            $('#reportedDate').datepicker({
                format: 'yyyy-mm-dd',
                endDate: FromEndDate,
                autoclose: true
            });
		});
	</script>
@stop
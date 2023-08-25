@extends('Admin.Master')
@section('content')
<style>
.str-cap {
	text-transform: capitalize;
}
</style>
<section class="content-header">
	<h1>
	{{trans('labels.business_claim_request')}}
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
                    <table class="table table-bordered" id="claimRequest" role="grid"></table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</section>
@stop
@section('script')	
	<script type="text/javascript">
		$(document).ready(function() {
            $('#claimRequest').DataTable( {
                hideEmptyCols: true,
                processing: true,
                serverSide: true,
                ajax: { 
                    url: "{{ route('entity.claim.request') }}"
                },
                aaSorting: [[0, 'desc']],
                columns: [
                    {data: 'id', name: 'id', title: 'Id'},
                    {data: 'user_name', name: 'claimBy.name', title: 'User Name'},
                    {data: 'claim_by.phone', name: 'claimBy.phone', title: 'Mobile'},
                    {data: 'entity_name', name: 'entity.name', title: 'Business Name'},
                    {data: 'created_at', name: 'created_at', title: 'Date'},
                    {data: 'document', name: 'document', title: 'Document',orderable:false,searchable:false},
                    {data: 'action', title: 'Action', orderable:false,searchable:false}
                ]
            });
            
            $(document).on("click",".update-claim-status",function(e) {
                e.preventDefault();
                var status = $(this).data('status');
                if( status == 'approved') {
                    var isConfirm = confirm('Are you sure you want to approve?');
                } else {
                    var isConfirm = confirm('Are you sure you want to reject?');
                }
                if(isConfirm) {
                    var url = $(this).data('url');
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {status:status, _token:'{{csrf_token()}}'},
                        success: function( response ) {
                            if(response.status) {
                                showSuccessMessage(response.message);
                            } else {
                                showErrorMessage(response.message);
                            }
                            $('#claimRequest').DataTable().ajax.reload();                            
                        }
                    });
                }
            });
		});        
	</script>
@stop
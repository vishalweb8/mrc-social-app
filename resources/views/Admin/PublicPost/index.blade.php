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
	Public Posts
    @can(config('perm.addPost'))
	<a href="{{ route('publicPost.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.public_post')}}</a>
    @endcan
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
            <div class="box-header">
                    <div class="col-md-2">
                        <select class="form-control" id="siteFilter">
                            <option value='' selected>All Site</option>  
                        </select>
                    </div>
            </div>
				<div class="box-body table-responsive">
					<div id="public_post_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">                        
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-bordered table-striped dataTable no-footer" id="public_post" role="grid" aria-describedby="public_post_info">
									<thead>
										<tr role="row">
											<th>
												Id
											</th>
											<th>
												User
											</th>
											<th>
												Category
											</th>
											<th>
												Type
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
								</table>
							</div>
						</div>
					</div>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
    <form id="delete-post-form" action="#" method="post" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</section>
@stop
@section('script')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
            $('#public_post').DataTable( {
                hideEmptyCols: true,
                processing: true,
                serverSide: true,
                ajax: { 
                    url: "{{ route('publicPost.index') }}",
                    data: function(data) {
                        data.site_id = $("#siteFilter").val();
                    }
                },
                aaSorting: [[0, 'desc']],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'user_name', name: 'user.name'},
                    {data: 'category', name: 'category'},
                    {data: 'type', name: 'type'},
                    {data: 'status', name: 'status'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'action', name: 'action',  orderable:false,searchable:false}
                ]
            });

            $(document).on("change","#siteFilter",function(e) {
                $('#public_post').DataTable().ajax.reload();         
            });

            $(document).on("click",".delete-post",function(e) {
                e.preventDefault();
                var isConfirm = confirm('Are you sure? you want to delete this.');
                if(isConfirm) {
                    $("#delete-post-form").attr('action',$(this).data('url'));
                    $("#delete-post-form").submit();
                }            
            });

            $('#siteFilter').select2({
                placeholder: 'All Site',
                allowClear: true,
                ajax: {
                    url: '{{ route("siteAutoComplete") }}',
                    dataType: 'json',
                    delay: 250,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });
		});
	</script>
@stop
@extends('Admin.Master')

@section('content')
<style>
.str-cap {
	text-transform: capitalize;
}
</style>
<section class="content-header">
	<h1>
	Sub Asset Types
	<a href="{{ route('subAssetType.create',request('id')) }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.sub_asset_type')}}</a>
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
                    <table class="table table-bordered" id="asset_type" role="grid"></table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
    <form id="delete-asset-form" action="#" method="post" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</section>
@stop
@section('script')
	<script type="text/javascript">
		$(document).ready(function() {
            $('#asset_type').DataTable( {
                hideEmptyCols: true,
                processing: true,
                serverSide: true,
                //searching: false,
                ajax: { 
                    url: "{{ route('getSubAssetTypeByAsset',request('id')) }}"
                },
                aaSorting: [[0, 'desc']],
                columns: [
                    {data: 'id', name: 'id', title: 'Id'},
                    {data: 'name', name: 'name', title: 'Name'},
                    {data: 'parent.name', name: 'parent', title: 'Parent Asset Type'},
                    {data: 'action', title: 'Action', orderable:false, searchable:false}
                ]
            });
		});
	</script>
    @include('Admin.AssetType.script')
@stop
@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>

        {{trans('labels.permissions')}}
        @can(config('perm.addPermission'))
        <a href="{{ route('permission.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.permission')}}</a>
        @endcan

    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="permissionList">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.name')}}</th>
                                <th>{{trans('labels.module')}}</th>
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div><!-- /.col -->
        <form id="delete-permission-form" action="#" method="post" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#permissionList').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('permission.index') }}"
            },
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'module', name: 'module'},
                {data: 'action', orderable:false,searchable:false}
            ]
        });

        $(document).on("click",".delete-permission",function(e) {
            e.preventDefault();
            var isConfirm = confirm('Are you sure? you want to delete this.');
            if(isConfirm) {
                $("#delete-permission-form").attr('action',$(this).data('url'));
                $("#delete-permission-form").submit();
            }            
        });
    });
</script>
@stop
@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>

        {{trans('labels.roles')}}
        @can(config('perm.addRole'))
        <a href="{{ route('role.create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.role')}}</a>
        @endcan

    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="roleList">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.name')}}</th>
                                <th>{{trans('labels.type')}}</th>
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div><!-- /.col -->
        <form id="delete-role-form" action="#" method="post" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#roleList').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('role.index') }}"
            },
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'type', name: 'type'},
                {data: 'action', orderable:false,searchable:false}
            ]
        });

        $(document).on("click",".delete-role",function(e) {
            e.preventDefault();
            var isConfirm = confirm('Are you sure? you want to delete this.');
            if(isConfirm) {
                $("#delete-role-form").attr('action',$(this).data('url'));
                $("#delete-role-form").submit();
            }            
        });
    });
</script>
@stop
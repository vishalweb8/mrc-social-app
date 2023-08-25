@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>

        {{trans('labels.cities')}}
        <a href="{{ url('admin/addcity') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.city')}}</a>

    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="cityList">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.state')}}</th>
                                <th>{{trans('labels.name')}}</th>
                                <th>{{trans('labels.latitude')}}</th>
                                <th>{{trans('labels.longitude')}}</th>
                                <th>{{trans('labels.position')}}</th>
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div><!-- /.col -->
        <form id="delete-city-form" action="#" method="post" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#cityList').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('city.index') }}"
            },
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'state.name', name: 'state.name'},
                {data: 'name', name: 'name'},
                {data: 'latitude', name: 'latitude'},
                {data: 'longitude', name: 'longitude'},
                {data: 'position', name: 'position'},
                {data: 'action', orderable:false,searchable:false}
            ]
        });

        $(document).on("click",".delete-city",function(e) {
            e.preventDefault();
            var isConfirm = confirm('Are you sure? you want to delete this.');
            if(isConfirm) {
                $("#delete-city-form").attr('action',$(this).data('url'));
                $("#delete-city-form").submit();
            }            
        });

        $('#cityList_filter input').unbind();
        $("#cityList_filter input").on('change',function(e) {
            $('#cityList').DataTable().search($(this).val()).draw();
        });
    });
</script>
@stop
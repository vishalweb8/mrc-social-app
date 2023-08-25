@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>

    {{trans('labels.states')}}
    @can(config('perm.addState'))
    <a href="{{ url('admin/addstate') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.state')}}</a>
    @endcan

    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="stateList">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.country')}}</th>
                                <th>{{trans('labels.name')}}</th>
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div><!-- /.col -->
        <form id="delete-state-form" action="#" method="post" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#stateList').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('state.index') }}"
            },
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'country.name', name: 'country.name'},
                {data: 'name', name: 'name'},
                {data: 'action', orderable:false,searchable:false}
            ]
        });

        $(document).on("click",".delete-state",function(e) {
            e.preventDefault();
            var isConfirm = confirm('Are you sure? you want to delete this.');
            if(isConfirm) {
                $("#delete-state-form").attr('action',$(this).data('url'));
                $("#delete-state-form").submit();
            }            
        });

        // fire event when datatable initialised
        $(document).on( 'init.dt', function ( e, settings ) {
            var api = new $.fn.dataTable.Api( settings );
            var table = '#'+api.table().node().id;
            $(table+'_filter input').unbind();
            $(table+'_filter input').on('change',function(e) {
                $(table).DataTable().search($(this).val()).draw();
            });
        } );        
    });
    
</script>
@stop
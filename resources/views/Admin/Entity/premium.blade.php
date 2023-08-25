@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.premiumbusinesses')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body table-responsive">
                    <table class="table table-hover" id="business">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.name')}}</th>
                                <th>{{trans('labels.user')}}</th>
                                <th>{{trans('labels.category')}}</th>
                                <th>{{trans('labels.mobile')}}</th>
                                <th>{{trans('labels.type')}}</th>
                                <th>Expity Date</th>
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
            
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#business').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('entity.premium') }}"
            },
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'user_name', name: 'user.name'},
                {data: 'categories', name: 'category_id',orderable:false,searchable:false},
                {data: 'country_code', name: 'mobile'},
                {data: 'membership_type', name: 'membership_type'},
                {data: 'plan', name: 'plan',orderable:false,searchable:false},
                {data: 'action', title: 'Action', orderable:false,searchable:false}
            ]
        });
    });
</script>
@stop

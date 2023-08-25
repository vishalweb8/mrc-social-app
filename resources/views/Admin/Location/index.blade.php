@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>

        {{trans('labels.location')}}
        @can(config('perm.addLocation'))
        <a href="{{ url('admin/location/create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.location')}}</a>
        @endcan

    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="locationList">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.country')}}</th>
                                <th>{{trans('labels.state')}}</th>
                                <th>{{trans('labels.district')}}</th>
                                <th>{{trans('labels.tehsil')}}</th>
                                <th>{{trans('labels.city')}}</th>
                                <th>{{trans('labels.pincode')}}</th>
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div><!-- /.col -->
        <form id="delete-location-form" action="#" method="post" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#locationList').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('location.index') }}"
            },
            aaSorting: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'country',
                    name: 'country'
                },
                {
                    data: 'state',
                    name: 'state'
                },
                {
                    data: 'district',
                    name: 'district'
                },
                {
                    data: 'tehsil',
                    name: 'tehsil'
                },
                {
                    data: 'city',
                    name: 'city'
                },
                {
                    data: 'pincode',
                    name: 'pincode'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $(document).on("click", ".delete-location", function(e) {
            e.preventDefault();
            var isConfirm = confirm('Are you sure? you want to delete this.');
            if (isConfirm) {
                $("#delete-location-form").attr('action', $(this).data('url'));
                $("#delete-location-form").submit();
            }
        });

    });
</script>
@stop
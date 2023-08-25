@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> 
        {{trans('labels.callToAction')}}
        @can(config('perm.addCallToAction'))
        <a href="{{ url('admin/calltoaction/create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.callToAction')}}</a>
        @endcan

    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="callToActionList">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.application')}}</th>
                                <th>{{trans('labels.name')}}</th>
                                <th>{{trans('labels.target')}}</th>
                                <th>{{trans('labels.placement')}}</th>
                                <th>{{trans('labels.icon')}}</th> 
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div><!-- /.col -->
        <form id="delete-callToAction-form" action="#" method="post" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#callToActionList').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('calltoaction.index') }}"
            },
            aaSorting: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'application.name',
                    name: 'application.name'
                },
                {
                    data: 'name',
                    name: 'name'
                },  
                {
                    data: 'target',
                    name: 'target'
                },
                {
                    data: 'placement',
                    name: 'placement'
                },  
                {
                    data: 'icon',
                    name: 'icon'
                }, 
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $(document).on("click", ".delete-callToAction", function(e) {
            e.preventDefault();
            var isConfirm = confirm('Are you sure? you want to delete this.');
            if (isConfirm) {
                $("#delete-callToAction-form").attr('action', $(this).data('url'));
                $("#delete-callToAction-form").submit();
            }
        });

    });
</script>
@stop
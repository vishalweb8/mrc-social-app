@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>

        {{trans('labels.jobs')}}
        @can(config('perm.addJobs'))
        <a href="{{ url('admin/jobs/create') }}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.jobs')}}</a>
        @endcan

    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="fetchList">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.title')}}</th>
                                <th>{{trans('labels.company_name')}}</th>
                                <th>{{trans('labels.qualification')}}</th>
                                <th>{{trans('labels.workplace_type')}}</th> 
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div><!-- /.col -->
        <form id="delete-jobs-form" action="#" method="post" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#fetchList').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('jobs.index') }}"
            },
            aaSorting: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                }, 
                {
                    data: 'qualification',
                    name: 'qualification'
                },
                {
                    data: 'workplace_type',
                    name: 'workplace_type'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $(document).on("click", ".delete-jobs", function(e) {
            e.preventDefault();
            var isConfirm = confirm('Are you sure? you want to delete this.');
            if (isConfirm) {
                $("#delete-jobs-form").attr('action', $(this).data('url'));
                $("#delete-jobs-form").submit();
            }
        });

    });
</script>
@stop
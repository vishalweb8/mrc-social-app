@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> 
        {{trans('labels.job_apply_list')}} 
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
                                <th>{{trans('labels.title')}}</th> 
                                <th>{{trans('labels.name')}}</th>
                                <th>{{trans('labels.email')}}</th>
                                <th>{{trans('labels.mobile_no')}}</th>
                                <th>{{trans('labels.experience')}}</th> 
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
                url: "{{ route('job-apply-list.index') }}"
            },
            aaSorting: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'jobvacancy.title',
                    name: 'jobvacancy.title'
                }, 
                {
                    data: 'user.name',
                    name: 'user.name'
                }, 
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'mobile_no',
                    name: 'mobile_no'
                },
                {
                    data: 'experience',
                    name: 'experience'
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
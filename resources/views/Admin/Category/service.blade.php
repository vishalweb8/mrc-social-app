@extends('Admin.Master')

@section('content')

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.trending_services')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                    <strong>{{trans('labels.whoops')}}</strong> {{trans('labels.someproblems')}}<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="box-body table-responsive">
                    <table id="trendingServicesListing" class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.lblcategoryname')}}</th>
                                <th>{{trans('labels.noofbusiness')}}</th>
                                <th>{{trans('labels.headerstatus')}}</th>
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    
    $(document).ready(function() {
        $('#trendingServicesListing').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('getAllTrendingServices') }}"
            },
            aaSorting: [[3, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'business_count', name: 'business_count',orderable:false,searchable:false},
                {data: 'trending_service', name: 'trending_service', class:'trending'},
                {data: 'action', orderable:false,searchable:false}
            ]
        });
    });
</script>

<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    function updateTrendingService(event,categroyId)
    {
        var checkBox = document.getElementById("trendingService_"+categroyId);
        var trendingService = 0;
        if (checkBox.checked == true)
        {
            trendingService = 1;            
        }    
        $.ajax({
            type: 'post',
            url: '{{ url("admin/updateTrendingService") }}',
            data: {
                categoryId: categroyId,
                trendingService:trendingService
            },
            success: function (response)
            {
                if(response !== '' && response != 0)
                {
                    var label = '-';
                    if(trendingService == 1) {
                        label = '<span class="label label-success">Trending</span>';
                    }
                    $(event).closest('tr').find('.trending').html(label);
                }
            }
        });
    }
</script>

@stop

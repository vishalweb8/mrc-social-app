@extends('Admin.Master')

@section('content')

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.trending_categories')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">
                <div class="box-body table-responsive">
                    <table id="trendingCategoriesListing" class='table table-bordered table-striped'>
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function() {
        $('#trendingCategoriesListing').DataTable( {
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('getAllTrendingCategory') }}"
            },
            aaSorting: [[3, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'business_count', name: 'business_count',orderable:false,searchable:false},
                {data: 'trending_category', name: 'trending_category', class:'trending'},
                {data: 'action', orderable:false,searchable:false}
            ]
        });
    });
    function updateTrendingCategory(event,categroyId)
    {
        var checkBox = document.getElementById("trendingCategory_"+categroyId);
        var trendingCategory = 0
        if (checkBox.checked == true)
        {
            trendingCategory = 1;            
        }  
        $.ajax({
            type: 'post',
            url: '{{ url("admin/updateTrendingCategory") }}',
            data: {
                categoryId: categroyId,
                trendingCategory:trendingCategory
            },
            success: function (response)
            {
                if(response !== '' && response != 0)
                {
                    var label = '-';
                    if(trendingCategory == 1) {
                        label = '<span class="label label-success">Trending</span>';
                    }
                    $(event).closest('tr').find('.trending').html(label);
                }
            }
        });
    }
</script>
@stop

@extends('Admin.Master')

@section('content')

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.lblcategorymanagement')}}
        <div class="pull-right">
            @can(config('perm.addCategory')) 
            <a href="{{url('admin/category/create')}}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;Add Category</a>
            @endcan
        </div>
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
                    <table id="categoryListing" class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.lblcategoryname')}}</th>
                                <th>{{trans('labels.catlogo')}}</th>
                                <th>{{trans('labels.bannerimage')}}</th>    
                                <th>{{trans('labels.noofbusiness')}}</th>
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
        $('#categoryListing').DataTable({
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('category.index') }}"
            },
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'category_logo', name: 'category_logo',orderable:false,searchable:false},
                {data: 'banner_img', name: 'banner_img',orderable:false,searchable:false},
                {data: 'business_count', name: 'business_count',orderable:false,searchable:false},
                {data: 'action', orderable:false,searchable:false}
            ]
        });
    });
</script>
@stop

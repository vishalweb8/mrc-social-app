@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.csvimport')}}
         
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                 <div class="box-header with-border">
                    <h3 class="box-title">Add {{trans('labels.csvimport')}}</h3>
                </div>

                 <form id="addbusiness" class="form-horizontal" method="post" action="{{ url('admin/csvimport') }}" enctype="multipart/form-data">
                    <div class="box-body">
   <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                             <label for="name" class="col-sm-2 control-label">{{trans('labels.name')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="file" class="form-control" id="file" name="file">
                            </div>
                            
                        </div>
                    </div>
                     <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                            <a class="btn btn-default" href="{{ url('/admin/users') }}">{{trans('labels.cancelbtn')}}</a>
                        </div>
                    </div><!-- /.box-footer -->
                 </form>
            </div>
    
            <table>
                @if($notInsert )
                <tr>
                    <td>Insert all ready Record </td>
                    <td>{{$notInsert}} </td>
                </tr>
                @endif
                @if($insertRecord )
                <tr>
                    <td>Insert Record </td>
                    <td>{{$insertRecord}} </td>
                </tr>
                 @endif
            </table>
@if($pendingRecord )
            <h2> This data already exist</h2>
            
             <table class="table table-hover dataTable no-footer">
                <tr>
                    <td>Row Number</td>
                    <td>Full Name</td>
                    <td>Email </td>
                    <td>Country Code </td>
                    <td> Mobile </td>
                    <td> Message </td>
                </tr>
               
                @foreach($pendingRecord as $val)
                <tr>
                     <td>{{$val['row']}}</td>
                    <td>{{$val['name']}} </td>
                    <td>{{$val['email']}} </td>
                    <td>{{$val['country_code']}} </td>
                    <td> {{$val['phone']}} </td>
                    <td> {{$val['msg']}} </td>
                </tr>
                @endforeach
                
            </table>
@endif 
                <!-- /.box-header
               
            <!-- /.box -->
            
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#subscriptionsplan').DataTable({
           "aaSorting": []
        });
    });
</script>
@stop
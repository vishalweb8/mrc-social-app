@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.setting')}}
         
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                 <div class="box-header with-border">
                    <h3 class="box-title">Add {{trans('labels.setting')}}</h3>
                </div>

                 <form id="addbusiness" class="form-horizontal" method="post" action="{{ url('admin/setting') }}" enctype="multipart/form-data">
                    <div class="box-body">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                             <label for="name" class="col-sm-2 control-label">{{trans('labels.whatsapp_message')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <textarea name="whatsapp_message" class="form-control">{{$cityList->whatsapp_message}} </textarea>
                            </div>
                            
                        </div>
                    </div>
                     <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.updatebtn')}}</button>
                            <a class="btn btn-default" href="{{ url('/admin/users') }}">{{trans('labels.cancelbtn')}}</a>
                        </div>
                    </div><!-- /.box-footer -->
                 </form>
            </div>
    
            

 
             
            
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#subscriptionsplan').DataTable({
            hideEmptyCols: true,
           "aaSorting": []
        });
    });
</script>
@stop
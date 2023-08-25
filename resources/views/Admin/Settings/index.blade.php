@extends('Admin.Master')

@section('header')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<link href="{{ asset('css/parsley.css') }}" rel="stylesheet" />

@endsection

@section('content')
<style>
	 .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Settings
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <form class="form-horizontal" method="post" action="{{ route('settings.save') }}" enctype="multipart/form-data" data-parsley-validate>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                @can(config('perm.viewSiteSetting'))
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Site Settings</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="business_id" class="col-sm-2 control-label">
                                OTP Enable
                            </label>
                            <div class="col-sm-6">
                                <label class="switch">
                                    <input type="checkbox" name="is_otp_enable"  autocomplete="off" value="1" @if(isset($settings['is_otp_enable']) && $settings['is_otp_enable']) checked @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="upi" class="col-sm-2 control-label">
                                UPI Enable
                            </label>
                            <div class="col-sm-6">
                                <label class="switch">
                                    <input type="checkbox" name="is_upi_enable"  autocomplete="off" value="1" @if(isset($settings['is_upi_enable']) && $settings['is_upi_enable']) checked @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="label" class="col-sm-2 control-label">
                            Other Languages <a href='#' data-html="true" data-toggle="popover"  data-trigger="focus"   data-content="Add other language with comma separated excluding english.">(Note)</a>
                            </label>
                            <div class="col-sm-6">
                                <input class="form-control" type="text" name="other_languages"  autocomplete="off" value="@if(isset($settings['other_languages'])){{$settings['other_languages']}}@endif" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="label" class="col-sm-2 control-label">
                                Post Categories
                            </label>
                            <div class="col-sm-6">
                                <input class="form-control" type="text" name="post_category"  autocomplete="off" value="@if(isset($settings['post_category'])){{$settings['post_category']}}@endif" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="label" class="col-sm-2 control-label">
                                Moderator Keywords
                            </label>
                            <div class="col-sm-6">
                                <input class="form-control" type="text" name="moderator_keywords"  autocomplete="off" value="@if(isset($settings['moderator_keywords'])){{$settings['moderator_keywords']}}@endif" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="label" class="col-sm-2 control-label">
                                Support Number
                            </label>
                            <div class="col-sm-6">
                                <input data-parsley-type="digits" data-parsley-length="[10,10]" data-parsley-length-message="Please enter 10 digit mobile number" class="form-control" type="text" name="support_number"  autocomplete="off" value="@if(isset($settings['support_number'])){{$settings['support_number']}}@endif" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="label" class="col-sm-2 control-label">
                                Support Email
                            </label>
                            <div class="col-sm-6">
                                <input data-parsley-type="email" class="form-control" type="text" name="support_email"  autocomplete="off" value="@if(isset($settings['support_email'])){{$settings['support_email']}}@endif" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.whatsapp_message')}}<span class="star_red">*</span></label>
                            <div class="col-sm-6">
                                <textarea name="whatsapp_message" class="form-control">@if(isset($settings['whatsapp_message'])){{$settings['whatsapp_message']}}@endif</textarea>
                            </div>
                            
                        </div>
                    </div>
                </div>
                @endcan
                @can(config('perm.viewAdminSetting'))
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Admin Settings</h3>
                    </div>
                    <div class="box-body">                        
                        <div class="form-group">
                            <label for="label" class="col-sm-2 control-label">
                            Module Names <a href='#' data-html="true" data-toggle="popover"  data-trigger="focus"   data-content="Add module names with comma separated.">(Note)</a>
                            </label>
                            <div class="col-sm-6">
                                <input class="form-control" type="text" name="modules"  autocomplete="off" value="@if(isset($settings['modules'])){{$settings['modules']}}@endif" >
                            </div>
                        </div>
                    </div>
                </div>
                @endcan
                @can(config('perm.editSetting'))
                @canany([config('perm.viewAdminSetting'),config('perm.viewSiteSetting')])
                <div class="box-footer">
                    <div class="pull-right">
                        <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                    </div>
                </div><!-- /.box-footer -->
                @endcanany
                @endcan
            </form>
            <!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
  
@section('script')
<script src="{{ asset('js/parsley.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    $(function() {
        $("[data-toggle=popover]").popover();
    });
</script>
@stop

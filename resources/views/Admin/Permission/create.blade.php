@extends('Admin.Master')
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.permission')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (!empty($permissions)) ? ' Edit ' : 'Add' ?> {{trans('labels.permission')}}</h3>
                </div>  <!-- .box-header -->
                <form id="permissionForm" class="form-horizontal" method="post" action="{{ route('permission.store') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    
                    <div class="box-body">
                        <div class="form-group">
                            <label for="module" class="col-sm-2 control-label">{{trans('labels.module')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <select name="module" class="form-control" >
                                    <option value="">Select Module</option>
                                    @foreach(getSettingsInArray('modules') as $module)
                                    <option value="{{$module}}" @if(old('module',$permissions->module ?? '') == $module) selected @endif>{{$module}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('module'))
                                    <span class="text-danger">{{ $errors->first('module') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Operations</label>
                            @foreach(getStdPermission() as $permission)
                                <div class="col-sm-1">
                                    <input type="checkbox" class="mr5" name="name[]" value="{{$permission}}"> {{$permission}}
                                </div>
                            @endforeach
                        </div>                      
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Custom {{trans('labels.name')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="name[]" placeholder="Custom permission name" value="">
                            </div>
                            <div class="col-sm-1">
                                <button type="button" class="btn bg-purple add-custom-permission">+</button>   
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                            <a class="btn btn-default" href="{{ route('permission.index') }}">{{trans('labels.cancelbtn')}}</a>
                        </div>
                    </div><!-- /.box-footer -->
                </form>
            </div>
        </div>
    </div>
</section><!-- /.content -->
@stop
@section('script')
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.0/additional-methods.js"></script>

<script type="text/javascript">
    $(function() {
        var validateRules = {
        // "name[]": { required: true, },
            module: { required: true }
        };
        $("#permissionForm").validate({
            ignore: "",
            rules: validateRules
        });

        $('.add-custom-permission').click(function() {

            var html = '<div class="form-group"><label for="activity_title" class="col-sm-2 control-label"></label><div class="col-sm-8"><input type="text" class="form-control" name="name[]" placeholder="Custom permission name" value=""></div><div class="col-sm-1"><button type="button" class="btn bg-purple remove-custom-field" >-</button></div></div></div>';

            $('.box-body').append(html);
        });
        $(document).on("click",".remove-custom-field",function() {
            $(this).closest('.form-group').remove();
        });
    });
</script>
@stop

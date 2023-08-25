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
                    <h3 class="box-title"><?php echo (!empty($permission)) ? ' Edit ' : 'Add' ?> {{trans('labels.permission')}}</h3>
                </div>  <!-- .box-header -->
                <form id="permissionForm" class="form-horizontal" method="post" action="{{ route('permission.update',$permission->id) }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    @method('put')
                    <div class="box-body">
                        <div class="form-group">
                            <label for="module" class="col-sm-2 control-label">{{trans('labels.module')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <select name="module" class="form-control" >
                                    <option value="">Select Module</option>
                                    @foreach(getSettingsInArray('modules') as $module)
                                    <option value="{{$module}}" @if(old('module',$permission->module ?? '') == $module) selected @endif>{{$module}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('module'))
                                    <span class="text-danger">{{ $errors->first('module') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.name')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="name" placeholder="Name" value="{{ old('name',$permission->name ?? '') }}">
                                @if ($errors->has('name'))
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
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
    var validateRules = {
        name: { required: true, },
        module: { required: true, }
    };
    $("#permissionForm").validate({
        ignore: "",
        rules: validateRules
    });
</script>


@stop

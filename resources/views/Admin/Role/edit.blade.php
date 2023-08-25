@extends('Admin.Master')
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.role')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <form id="roleForm" class="form-horizontal" method="post" action="{{ route('role.store') }}">
        <!-- right column -->
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo (!empty($role)) ? ' Edit ' : 'Add' ?> {{trans('labels.role')}}</h3>
                    </div>  <!-- .box-header -->
                    
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="{{ $role->id ?? 0}}">
                    
                    <div class="box-body">
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.name')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="name" placeholder="Name" value="{{ old('name',$role->name ?? '') }}">
                                @if ($errors->has('name'))
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.type')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <select id="type" name="type" class="form-control" style="cursor: pointer;" >
                                    <option value="" selected>Select {{trans('labels.type')}}</option>
                                    <option value="admin" @if(isset($role) && $role->type == 'admin') selected @endif>Admin</option>
                                    <option value="frontend" @if(isset($role) && $role->type == 'frontend') selected @endif>Front-End</option>
                                    <option value="site" @if(isset($role) && $role->type == 'site') selected @endif>Site</option>
                                    
                                </select>
                                @if ($errors->has('type'))
                                    <span class="text-danger">{{ $errors->first('type') }}</span>
                                @endif
                            </div>
                        </div>                        
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{trans('labels.permissions')}}</h3>
                    </div>
                    <div class="box-body">
                        @foreach($permissions as $key => $permission)
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title" style="font-size: 15px;">{{($key == 'Advertise') ? 'Advertise/Website Inquiry' : $key}} ({{ count(array_intersect($permission->pluck('id')->toArray(),$assignedPermissions))}})</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="form-group">
                                    @foreach($permission as $operation)
                                        <div class="col-sm-2">
                                            <input type="checkbox" class="mr5" name="permissions[]" value="{{$operation->id}}" @if(in_array($operation->id,$assignedPermissions)) checked @endif> {{str_replace(' '.$key,'',$operation->name)}}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach  
                    </div>
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                            <a class="btn btn-default" href="{{ route('role.index') }}">{{trans('labels.cancelbtn')}}</a>
                        </div>
                    </div><!-- /.box-footer -->
                </div>            
            </div>
        </form>
    </div>
</section><!-- /.content -->
@stop
@section('script')
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.0/additional-methods.js"></script>

<script type="text/javascript">
    var validateRules = {
        name: { required: true, }
    };
    $("#roleForm").validate({
        ignore: "",
        rules: validateRules
    });
</script>


@stop

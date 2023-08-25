@extends('Admin.Master')

@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.tag-editor.css') }}" />
<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.callToAction')}}
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
                    <h3 class="box-title"><?php echo (isset($calltoaction) && !empty($calltoaction)) ? ' Edit ' : 'Add' ?> {{trans('labels.callToAction')}}</h3>
                </div> <!-- .box-header -->
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
                <form class="form-horizontal" method="post" action="{{route('calltoaction.store')}}" enctype="multipart/form-data" novalidate="novalidate">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{$calltoaction->id ?? ''}}" />
                    <input type="hidden" name="old_icon" value="<?php echo (isset($calltoaction) && !empty($calltoaction) && $calltoaction->icon != '') ? $calltoaction->icon : '' ?>">

                    <div class="box-body">
                        <div class="form-group">
                            <?php
                            if (old('application_id'))
                                $application_id = old('application_id');
                            elseif (isset($calltoaction) && !empty ($calltoaction->application_id))
                                $application_id = $calltoaction->application_id;
                            else
                                $application_id = '';
                            ?>
                            <label for="application_id" class="col-sm-2 control-label">{{ trans('labels.application') }}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <select name="application_id" data="" class="form-control">
                                    <option value="">Select {{trans('labels.application')}}</option>
                                    @forelse($applications as $app)
                                        <option class="type_parent_cat cat_type" value="{{$app->id}}" {{($application_id == $app->id)? 'selected' : ''}}>
                                            {{$app->name}}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                                if (old('name'))
                                    $name = old('name');
                                elseif (isset($calltoaction) && !empty ($calltoaction->name))
                                    $name = $calltoaction->name;
                                else
                                    $name = '';
                            ?>
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.name')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" placeholder="Enter name" class="form-control" id="name" name="name" value="{{$name}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                                if (old('target'))
                                    $target = old('target');
                                elseif (isset($calltoaction) && !empty ($calltoaction->target))
                                    $target = $calltoaction->target;
                                else
                                    $target = '';
                            ?>
                            <label for="target" class="col-sm-2 control-label">{{trans('labels.target')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" placeholder="Enter target" class="form-control" id="target" name="target" value="{{$target}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                                if (old('placement'))
                                    $placement = old('placement');
                                elseif (isset($calltoaction) && !empty ($calltoaction->placement))
                                    $placement = $calltoaction->placement;
                                else
                                    $placement = '';
                            ?>
                            <label for="placement" class="col-sm-2 control-label">{{trans('labels.placement')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <select name="placement" data="" class="form-control">
                                    <option value="">Select {{trans('labels.placement')}}</option> 
                                        <option class="type_parent_cat cat_type" value="homepageTop" {{($placement == 'homepageTop')? 'selected' : ''}}>Homepage Top </option> 
                                        <option class="type_parent_cat cat_type" value="homepageCenter" {{($placement == 'homepageCenter')? 'selected' : ''}}>Homepage Center </option> 
                                        <option class="type_parent_cat cat_type" value="homepageBottom" {{($placement == 'homepageBottom')? 'selected' : ''}}>Homepage Bottom </option> 
                                </select>
                            </div>  
                        </div>
                        <div class="form-group">
                            <?php
                                if (old('icon'))
                                    $icon = old('icon');
                                elseif (isset($calltoaction) && !empty ($calltoaction->icon))
                                    $icon = $calltoaction->icon;
                                else
                                    $icon = '';
                            ?>
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.icon')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="file" id="icon" name="icon">
                            </div>
                        </div>
                        @if(isset($calltoaction) && !empty($calltoaction))
                            @if($calltoaction->icon != '')
                                <div class="form-group" id="icon">
                                    <label for="media_images" class="col-sm-2 control-label">&nbsp;</label>
                                    <div class="col-sm-8">
                                        @if(Storage::disk(config('constant.DISK'))->exists(Config::get('constant.ICON_IMAGE_PATH').$calltoaction->icon)) 
                                            <img src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.ICON_IMAGE_PATH').$calltoaction->icon) }}" width="50" height="50"/>
                                        @endif                                                
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="box-footer">
                            <div class="pull-right">
                                <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                                <a class="btn btn-default" href="{{ url('/admin/calltoaction') }}">{{trans('labels.cancelbtn')}}</a>
                            </div>
                        </div><!-- /.box-footer -->
                </form>
            </div>
        </div>
    </div>
</section><!-- /.content -->
@stop
@section('script')

@stop
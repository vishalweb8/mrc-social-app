@extends('Admin.Master')

@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.tag-editor.css') }}" />
<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.application')}}
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
                    <h3 class="box-title"><?php echo (isset($application) && !empty($application)) ? ' Edit ' : 'Add' ?> {{trans('labels.application')}}</h3>
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
                <form class="form-horizontal" method="post" action="{{route('application.store')}}" enctype="multipart/form-data" novalidate="novalidate">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{$application->id ?? ''}}" />

                    <div class="box-body">
                    <div class="form-group">
                            <?php
                                if (old('name'))
                                    $name = old('name');
                                elseif (isset($application) && !empty ($application->name))
                                    $name = $application->name;
                                else
                                    $name = '';
                            ?>
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.name')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" placeholder="Enter application name" class="form-control" id="name" name="name" value="{{$name}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                                if (old('slug'))
                                    $slug = old('slug');
                                elseif (isset($application) && !empty ($application->slug))
                                    $slug = $application->slug;
                                else
                                    $slug = '';
                            ?>
                            <label for="slug" class="col-sm-2 control-label">{{trans('labels.slug')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" placeholder="Enter slug" class="form-control" id="slug" name="slug" value="{{$slug}}">
                            </div>
                        </div>
 

                        <div class="box-footer">
                            <div class="pull-right">
                                <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                                <a class="btn btn-default" href="{{ url('/admin/application') }}">{{trans('labels.cancelbtn')}}</a>
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
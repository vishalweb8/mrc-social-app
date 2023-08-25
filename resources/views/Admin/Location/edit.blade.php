@extends('Admin.Master')

@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.tag-editor.css') }}" />
<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.location')}}
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
                    <h3 class="box-title"><?php echo (isset($location) && !empty($location)) ? ' Edit ' : 'Add' ?> {{trans('labels.location')}}</h3>
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
                <form class="form-horizontal" method="post" action="{{route('location.store')}}" enctype="multipart/form-data" novalidate="novalidate">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{$location->id ?? ''}}" />

                    <div class="box-body">
                        <div class="form-group">

                            <div class="col-md-4">
                                <?php
                                if (old('country'))
                                    $country = old('country');
                                elseif (isset($location) && !empty($location->country))
                                    $country = $location->country;
                                else
                                    $country = '';
                                ?>
                                <label for="country" class="control-label">{{trans('labels.country')}}<span class="star_red">*</span></label>
                                <div>
                                    <input type="text" class="form-control" id="country" name="country" value="{{$country}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('country_code'))
                                    $country_code = old('country_code');
                                elseif (isset($location) && !empty($location->country_code))
                                    $country_code = $location->country_code;
                                else
                                    $country_code = '';
                                ?>
                                <label for="country_code" class="control-label">{{trans('labels.countrycode')}}<span class="star_red">*</span></label>
                                <div>
                                    <input type="text" class="form-control" id="country_code" name="country_code" value="{{$country_code}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('state'))
                                    $state = old('state');
                                elseif (isset($location) && !empty($location->state))
                                    $state = $location->state;
                                else
                                    $state = '';
                                ?>
                                <label for="state" class="control-label">{{trans('labels.state')}}<span class="star_red">*</span></label>
                                <div>
                                    <input type="text" class="form-control" id="state" name="state" value="{{$state}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-4 ">
                                <?php
                                if (old('district'))
                                    $district = old('district');
                                elseif (isset($location) && !empty($location->district))
                                    $district = $location->district;
                                else
                                    $district = '';
                                ?>
                                <label for="district" class="control-label">{{trans('labels.district')}}<span class="star_red">*</span></label>
                                <div>
                                    <input type="text" class="form-control" id="district" name="district" value="{{$district}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('tehsil'))
                                    $tehsil = old('tehsil');
                                elseif (isset($location) && !empty($location->tehsil))
                                    $tehsil = $location->tehsil;
                                else
                                    $tehsil = '';
                                ?>
                                <label for="tehsil" class="control-label">{{trans('labels.tehsil')}} </label>
                                <div>
                                    <input type="text" class="form-control" id="tehsil" name="tehsil" value="{{$tehsil}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('city'))
                                    $city = old('city');
                                elseif (isset($location) && !empty($location->city))
                                    $city = $location->city;
                                else
                                    $city = '';
                                ?>
                                <label for="city" class="control-label">{{trans('labels.city')}}<span class="star_red">*</span></label>
                                <div>
                                    <input type="text" class="form-control" id="city" name="city" value="{{$city}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-4 ">
                                <?php
                                if (old('locality'))
                                    $locality = old('locality');
                                elseif (isset($location) && !empty($location->locality))
                                    $locality = $location->locality;
                                else
                                    $locality = '';
                                ?>
                                <label for="locality" class="control-label">{{trans('labels.locality')}}</label>
                                <div>
                                    <input type="text" class="form-control" id="locality" name="locality" value="{{$locality}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('pincode'))
                                    $pincode = old('pincode');
                                elseif (isset($location) && !empty($location->pincode))
                                    $pincode = $location->pincode;
                                else
                                    $pincode = '';
                                ?>
                                <label for="pincode" class="control-label">{{trans('labels.pincode')}}<span class="star_red">*</span></label>
                                <div>
                                    <input type="text" class="form-control" id="pincode" name="pincode" value="{{$pincode}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('latitude'))
                                    $latitude = old('latitude');
                                elseif (isset($location) && !empty($location->latitude))
                                    $latitude = $location->latitude;
                                else
                                    $latitude = '';
                                ?>
                                <label for="latitude" class="control-label">{{trans('labels.latitude')}}</label>
                                <div>
                                    <input type="text" class="form-control" id="latitude" name="latitude" value="{{$latitude}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-4 ">
                                <?php
                                if (old('longitude'))
                                    $longitude = old('longitude');
                                elseif (isset($location) && !empty($location->longitude))
                                    $longitude = $location->longitude;
                                else
                                    $longitude = '';
                                ?>
                                <label for="longitude" class="control-label">{{trans('labels.longitude')}}</label>
                                <div>
                                    <input type="text" class="form-control" id="longitude" name="longitude" value="{{$longitude}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('type'))
                                    $type = old('type');
                                elseif (isset($location) && !empty($location->type))
                                    $type = $location->type;
                                else
                                    $type = '';
                                ?>
                                <label for="type" class="control-label">{{trans('labels.type')}}</label>
                                <div>
                                    <input type="text" class="form-control" id="type" name="type" value="{{$type}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('position'))
                                    $position = old('position');
                                elseif (isset($location) && !empty($location->position))
                                    $position = $location->position;
                                else
                                    $position = '';
                                ?>
                                <label for="position" class="control-label">{{trans('labels.position')}}</label>
                                <div>
                                    <input position="text" class="form-control" id="position" name="position" value="{{$position}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('flag'))
                                    $flag = old('flag');
                                elseif (isset($position) && !empty($position->flag))
                                    $flag = $position->flag;
                                else
                                    $flag = '';
                                ?>
                                <label for="name" class="control-label">{{trans('labels.flag')}}</label>
                                <div>
                                    <input type="file" id="flag" name="flag">
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <div class="pull-right">
                                <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                                <a class="btn btn-default" href="{{ url('/admin/location') }}">{{trans('labels.cancelbtn')}}</a>
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

<!-- Include tags in input box -->
<script src="{{ asset('js/jquery.caret.min.js') }}"></script>
<script src="{{ asset('js/jquery.tag-editor.js') }}"></script>
<script src="{{ asset('js/jquery.mask.min.js') }}"></script>



<script type="text/javascript">
    var validateRules = {
        name: {
            required: true,
        },
        country_id: {
            required: true
        }
    };
    $("#addStateManagement").validate({
        ignore: "",
        rules: validateRules,
        messages: {
            name: {
                required: "<?php echo trans('labels.statenamerequired'); ?>"
            },
            country_id: {
                required: "<?php echo trans('labels.countryrequired'); ?>"
            }
        }
    });
</script>


@stop
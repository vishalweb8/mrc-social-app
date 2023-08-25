@extends('Admin.Master')

@section('content')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css">

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <?php echo (isset($agent_approved)) ? trans('labels.agent') : trans('labels.user') ?>
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
                    <h3 class="box-title"><?php echo (isset($data) && !empty($data)) ? ' Edit ' : 'Add' ?> <?php echo (isset($agent_approved)) ? trans('labels.agent') : trans('labels.user') ?></h3>
                </div><!-- /.box-header -->
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>{{trans('labels.whoops')}}</strong> {{trans('labels.someproblems')}}<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form id="adduser" class="form-horizontal" method="post" action="{{ url('/admin/saveuser') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($data) && !empty($data)) ? $data->id : '0' ?>">
                    <input type="hidden" name="old_profile_pic" value="<?php echo (isset($data) && !empty($data) && $data->profile_pic != '') ? $data->profile_pic : '' ?>">
                    <input type="hidden" name="agent_approved" value="<?php echo (isset($agent_approved)) ? $agent_approved : 0 ?>">
                    <div class="box-body">

                        <div class="form-group">
                            <?php
                            if (old('name'))
                                $name = old('name');
                            elseif (isset($data))
                                $name = $data->name;
                            else
                                $name = '';
                            ?>
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.name')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{trans('labels.name')}}" value="{{$name}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('phone'))
                                $phone = old('phone');
                            elseif (isset($data))
                                $phone = $data->phone;
                            else
                                $phone = '';

                            if (old('country_code'))
                                $country_code = old('country_code');
                            elseif (isset($data))
                                $country_code = $data->country_code;
                            else
                                $country_code = Config::get('constant.INDIA_CODE');
                            ?>
                            <label for="phone" class="col-sm-2 control-label">{{trans('labels.phone')}}<span class="star_red">*</span></label>
                            <div class="col-sm-2">
                                    <?php $countryCodes = Helpers::getCountries(); ?>
                                    <select name="country_code" data="" class="form-control select2" id="country_code">
                                        <option value="">Country Code</option>
                                        @forelse($countryCodes as $codes)
                                            <option value="{{$codes->country_code}}" {{($country_code == $codes->country_code)?'selected':''}}>{{$codes->name}} {{$codes->country_code}} </option>
                                        @empty
                                        @endforelse
                                    </select>
                            </div>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="{{trans('labels.phone')}}" value="{{$phone}}">
                                <div class="phoneerror"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="col-sm-2 control-label">{{trans('labels.password')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="password" autocomplete="new-password" class="form-control" id="password" name="password" placeholder="{{trans('labels.password')}}" value="">
                                <div class="phoneerror"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                            if (old('roles'))
                                $roles = old('roles');
                            elseif (isset($data))
                                $roles = $data->roles->pluck('id')->toArray();
                            else
                                $roles = [];
                            ?>
                            <label for="roles" class="col-sm-2 control-label">{{trans('labels.roles')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <select name="roles[]" id="roles"  class="form-control" multiple>
                                    <option value="">Select {{trans('labels.roles')}}</option>
                                    @forelse(getRoles() as $role)
                                        <option class="" value="{{$role->id}}" {{(in_array($role->id,$roles))?'selected': ''}}>
                                            {{$role->name}}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <?php
                            if (old('designation'))
                                $designation = old('designation');
                            elseif (isset($data))
                                $designation = $data->designation;
                            else
                                $designation = '';
                            ?>
                        <div class="form-group">
                            <label for="designation" class="col-sm-2 control-label">Designation </label>
                            <div class="col-sm-8">
                               <input type="text" class="form-control" id="designation" name="designation" placeholder="Designation" value="{{$designation}}">
                                 
                            </div>
                        </div>
                        <?php
                            if (old('work_company'))
                                $work_company = old('work_company');
                            elseif (isset($data))
                                $work_company = $data->work_company;
                            else
                                $work_company = '';
                            ?>
                        <div class="form-group">
                            <label for="work_company" class="col-sm-2 control-label">Work Company </label>
                            <div class="col-sm-8">
                               <input type="text" class="form-control" id="work_company" name="work_company" placeholder="Work Company" value="{{$work_company}}">
                                 
                            </div>
                        </div>
                        <?php
                            if (old('education'))
                                $education = old('education');
                            elseif (isset($data))
                                $education = $data->education;
                            else
                                $education = '';
                            ?>
                        <div class="form-group">
                            <label for="education" class="col-sm-2 control-label">Education </label>
                            <div class="col-sm-8">
                               <input type="text" class="form-control" id="education" name="education" placeholder="Education" value="{{$education}}">
                                 
                            </div>
                        </div>

                        <?php
                            if (old('associated_orgs'))
                                $associated_orgs = old('associated_orgs');
                            elseif (isset($data))
                                $associated_orgs = $data->associated_orgs;
                            else
                                $associated_orgs = '';
                            ?>
                        <div class="form-group">
                            <label for="associated_orgs" class="col-sm-2 control-label">Associated Orgs </label>
                            <div class="col-sm-8">
                               <input type="text" class="form-control" id="associated_orgs" name="associated_orgs" placeholder="Associated Orgs" value="{{$associated_orgs}}">
                                 
                            </div>
                        </div>
                        <?php
                            if (old('interest'))
                                $interest = old('interest');
                            elseif (isset($data))
                                $interest = $data->interest;
                            else
                                $interest = '';
                            ?>
                        <div class="form-group">
                            <label for="interest" class="col-sm-2 control-label">Interest </label>
                            <div class="col-sm-8">
                               <input type="text" class="form-control" id="interest" name="interest" placeholder="Interest" value="{{$interest}}">
                                 
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                            if (old('country'))
                                $country = old('country');
                            elseif (isset($data))
                                $country = $data->country;
                            else
                                $country = '';
                            ?>
                            <label for="country" class="col-sm-2 control-label">{{trans('labels.country')}}</label>
                            <div class="col-sm-8">
                                <?php $countries = Helpers::getCountries(); ?>
                                    <select name="country" id="address_country" class="form-control select2 select_contury">
                                        <option value="">Select {{trans('labels.country')}}</option>
                                        @forelse($countries as $con)
                                            <option class="type_parent_cat cat_type" value="{{$con->country}}" {{($country == $con->name)?'selected': ''}}>
                                                {{$con->country}}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('state'))
                                $state = old('state');
                            elseif (isset($data))
                                $state = $data->state;
                            else
                                $state = '';
                            ?>
                            <label for="state" class="col-sm-2 control-label">{{trans('labels.state')}}</label>
                            <div class="col-sm-8">
                                <?php 
                                //use Illuminate\Support\Facades\DB;
                                // if($country){
                                    
                                //      $countryid =  DB::table('country')->select('id')->where('name',$country)->first(); 
                                //          $cid = $countryid->id;
                                // }else{
                                //     $cid = '';
                                // }
                                $states = Helpers::getStates(); ?>
                                 <select name="state" id="address_state" class="form-control select2">
                                    <option value="">Select {{trans('labels.state')}}</option>
                                    @forelse($states as $st)
                                
                                        <option class="type_parent_cat cat_type" value="{{$st->name}}" {{($state == $st->state)?'selected':''}}>
                                            {{$st->state}}
                                        </option>
                                    @empty
                                    @endforelse 
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <?php
                            if (old('city'))
                                $city = old('city');
                            elseif (isset($data))
                                $city = $data->city;
                            else
                                $city = '';
                            ?>
                            <label for="city" class="col-sm-2 control-label">{{trans('labels.city')}}</label>
                            <div class="col-sm-8">
                                <?php 
                                if($state){
                                $stateid =  DB::table('state')->select('id')->where('name',$state)->first(); 
                                           $sid = $stateid->id;
                                }else{
                                    $sid ='';
                                }
                                $cities = Helpers::getCities(); ?>
                                    <select id="address_city" name="city" class="form-control select2">
                                        <option value="">Select {{trans('labels.city')}}</option>
                                     @forelse($cities as $ct)
                                        @if($sid == $ct->state_id )
                                            <option class="type_parent_cat cat_type" value="{{$ct->city}}" {{($city == $ct->name)?'selected':''}}>
                                                {{$ct->city}}
                                            </option>
                                        @endif
                                        @empty
                                        @endforelse 
                                    </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <?php
                            if (old('district'))
                                $district = old('district');
                            elseif (isset($data))
                                $district = $data->district;
                            else
                                $district = '';
                            ?>
                            <label for="email" class="col-sm-2 control-label">{{trans('labels.district')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control"  name="district" placeholder="{{trans('labels.district')}}" value="{{$district}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('email'))
                                $email = old('email');
                            elseif (isset($data))
                                $email = $data->email;
                            else
                                $email = '';
                            ?>
                            <label for="email" class="col-sm-2 control-label">{{trans('labels.email')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="email" name="email" placeholder="{{trans('labels.email')}}" value="{{$email}}">
                            <div class="emailerror"></div>
                            </div>
                        </div>

                         <div class="form-group">
                            <?php
                            if (old('gender'))
                                $gender = old('gender');
                            elseif (isset($data))
                                $gender = $data->gender;
                            else
                                $gender = '';
                            ?>
                            <label for="gender" class="col-sm-2 control-label">{{trans('labels.gender')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="radio"  name="gender" value="1" <?php if($gender == 1){?> checked <?php } ?>>{{trans('labels.male')}}
                                <input type="radio"  name="gender" value="2" <?php if($gender == 2){?> checked <?php } ?>>{{trans('labels.female')}}
                                <input type="radio"  name="gender" value="3" <?php if($gender == 3){?> checked <?php } ?>>{{trans('labels.other')}}
                                <div id="gender_error">
                                    <em class="invalid"></em>
                                </div>
                            </div>
                            
                        </div>
                       


                        <div class="form-group">
                            <?php
                            if (old('profile_pic'))
                                $profile_pic = old('profile_pic');
                            elseif (isset($data))
                                $profile_pic = $data->profile_pic;
                            else
                                $profile_pic = '';
                            ?>
                            <label for="profile_pic" class="col-sm-2 control-label">{{trans('labels.profilepic')}}</label>
                            <div class="col-sm-8">
                                <input type="file" id="profile_pic" name="profile_pic">
                            </div>
                        </div>

                        @if(isset($data) && !empty($data))
                            <div class="form-group" id="business_images">
                                <label for="media_images" class="col-sm-2 control-label">&nbsp;</label>
                                <div class="col-sm-8">
                                    @if($data->profile_pic != '' && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$data->profile_pic))
                                        <img src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$data->profile_pic) }}" width="50" height="50"/>
                                    @else
                                        <img src="{{ url('images/default.png') }}" width="50" height="50"/>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <?php
                            if (old('dob'))
                                $dob = old('dob');
                            elseif (isset($data))
                                $dob = $data->dob;
                            else
                                $dob = '';

                            if($dob == '0000-00-00')
                                $dob = '';
                            
                            ?>
                            <label for="dob" class="col-sm-2 control-label">{{trans('labels.dob')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="birthdate" name="dob" placeholder="{{trans('labels.dob')}}" value="{{$dob}}">
                                <div class="doberror"></div>
                            </div>
                        </div>
                        
                        <!-- <div class="form-group">
                            <?php
                            if (old('occupation'))
                                $occupation = old('occupation');
                            elseif (isset($data))
                                $occupation = $data->occupation;
                            else
                                $occupation = '';
                            ?>
                            <label for="occupation" class="col-sm-2 control-label">{{trans('labels.occupation')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="occupation" name="occupation" placeholder="{{trans('labels.occupation')}}" value="{{$occupation}}">
                                <div class="occupationerror"></div>
                            </div>
                        </div> -->

                        <div class="form-group">
                            <?php
                            if (old('subscription'))
                                $subscription = old('subscription');
                            elseif (isset($data))
                                $subscription = $data->subscription;
                            else
                                $subscription = '';
                            ?>
                            <label for="subscription" class="col-sm-2 control-label">{{trans('labels.newsletter')}} {{trans('labels.subscription')}}</label>
                            <div class="col-sm-8">
                                <input type="checkbox" class="check_box_set" name="subscription" {{($subscription == 1)?'checked':''}}>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('isRajput'))
                                $isRajput = old('isRajput');
                            elseif (isset($data))
                                $isRajput = $data->isRajput;
                            else
                                $isRajput = 1;
                            ?>
                            <label for="isRajput" class="col-sm-2 control-label">{{trans('labels.are_you_belong_rajasthan')}}</label>
                            <div class="col-sm-8">
                                <input type="checkbox" class="check_box_set" name="isRajput" {{($isRajput == 1)?'checked':''}}>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('status'))
                                $status = old('status');
                            elseif (isset($data))
                                $status = $data->status;
                            else
                                $status = 1;
                            ?>
                            <label for="isRajput" class="col-sm-2 control-label">{{trans('labels.status')}}</label>
                            <div class="col-sm-8">
                                <select name="status" data="" class="form-control">
                                    <option value="1" {{($status == 1) ? 'selected' : ''}}>Active</option>
                                    <option value="0" {{($status == 0) ? 'selected' : ''}}>Deactive</option>
                                </select>
                            </div>
                        </div>

                        @if(isset($data) && !empty($data) && $data->id != '')
                        <hr/>
                        <div class="form-group">
                            <label for="metatags" class="col-sm-2 control-label">Make User as Agent?</label>
                            <div class="col-sm-8">
                                <input type="radio" class="check_box_set" value="1" name="action" {{($data->agent_approved == 1)?'checked':''}}> Yes
                                <input type="radio" class="check_box_set" value="0" name="action" {{($data->agent_approved == 0)?'checked':''}}> No
                            </div>
                        </div>

                        @if($data->agent_approved == 1)
                            <div class="agentUser" style="display: inline;">
                        @else
                            <div class="agentUser"  style="display: none;">
                        @endif
                        
                            <div class="form-group">
                                <?php
                                if (isset($data->agentUser->city))
                                    $agentCity = $data->agentUser->city;
                                else
                                    $agentCity = '';
                                ?>
                                <label for="agentCity" class="col-sm-2 control-label">{{trans('labels.agentcity')}}</label>
                                <div class="col-sm-8">
                                    <?php $cities = Helpers::getCities(); ?>
                                        <select id="address_city" name="agent_city[]" class="form-control select2" multiple>
                                            <option value="">Select {{trans('labels.city')}}</option>
                                            @forelse($cities as $ct)
                                                <option class="type_parent_cat cat_type" value="{{$ct->name}}" {{(in_array($ct->name,explode(',',$agentCity)))?'selected':''}}>
                                                    {{$ct->name}}
                                                </option>
                                            @empty
                                            @endforelse
                                        </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php
                                if (isset($data->agentUser->bank_detail))
                                    $agentBankDetail = $data->agentUser->bank_detail;
                                else
                                    $agentBankDetail = '';
                                ?>
                                <label for="agentBankDetail" class="col-sm-2 control-label">{{trans('labels.bankdescription')}}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="agentBankDetail" name="agent_bank_detail" placeholder="{{trans('labels.bankdescription')}}">{{$agentBankDetail}}</textarea>
                                </div>
                            </div>
                        </div>

                        @endif
                        <div class="form-group">
                            @php
                                $sqlQuery = isset($data) ? $data->sql_query : '';
                            @endphp
                            <label for="sql_query" class="col-sm-2 control-label">SQL Query  <a href='javascript:' data-html="true" data-toggle="popover"  data-trigger="focus"   data-content="Write sql query for filter listing data, main table is business. ex. business.city like 'Ahmedabad'"><i class="fa fa-info-circle" aria-hidden="true" ></i></a> </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control"  name="sql_query" placeholder="Sql query" value="{{old('sql_query',$sqlQuery)}}">
                            </div>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                            <a class="btn btn-default" href="{{ url('/admin/users') }}">{{trans('labels.cancelbtn')}}</a>
                        </div>
                    </div><!-- /.box-footer -->
                </form>
            </div>
        </div>
    </div>
</section><!-- /.content -->
@stop
@section('script')
<script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
<script src="{{ asset('js/jquery.mask.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>

<script>
$(function() {
    $("#address_country").change(function() {
            var selected_country =  $('option:selected', this).val();
            //alert(selected_country);
        var token = '<?php echo csrf_token() ?>';
        $.ajax({
            headers: { 'X-CSRF-TOKEN': token },
            type: "POST",
            url: "{{url('/admin/getState')}}",
            data: {selected_country: selected_country},
            success: function( data ) {
                
                $('#address_state').html(data);
                //alert(data);

              
            }
        });     
            
        });
     
    
     $("#address_state").change(function() {
            var selected_state =  $('option:selected', this).val();
             //alert(selected_state);
        var token = '<?php echo csrf_token() ?>';
        $.ajax({
            headers: { 'X-CSRF-TOKEN': token },
            type: "POST",
            url: "{{url('/admin/getCity')}}",
            data: {selected_state: selected_state},
            success: function( data ) {
                
                $('#address_city').html(data);
                //alert(data);

              
            }
        });     
            
        });
    });
$(document).ready(function () {
    $("[data-toggle=popover]").popover({ container: 'body'});
    
    $('input[name="action"]').on('click', function(e) {
        if($( "input[name='action']:checked" ).val() == 1)
        {
            $('.agentUser').show();
        }
        if($( "input[name='action']:checked" ).val() == 0)
        {
            $('.agentUser').hide();
        }
    });


    $('.select2').select2();
    $('#roles').select2({
        placeholder: 'Select Roles'
    });
    //$('#phone').mask('9999999999');
    var id = <?php echo (isset($data) && !empty($data)) ? $data->id : '0'; ?>;
    if(id == 0)
    {
        var userRules = {
            name: {
                required: true
            },
            email: {
                email: true
            },
            phone:{
                required: true,
                digits: true,
                maxlength: 13,
                minlength: 6,
                
            },
            password: {
                required: true,
                minlength: 8
            },
            gender: {
                required: true
            },
            "roles[]": {
                required: true
            }

        };
    }
    else
    {
        var userRules = {
            name: {
                required: true
            },
            email: {
                email: true
            },
            phone:{
                required: true,
                digits: true,
                maxlength: 13,
                minlength: 6,
            },
            password: {
                minlength: 8
            },
            gender: {
                required: true
            },
            "roles[]": {
                required: true
            }
        };
    }


    var FromEndDate = new Date();
    $('#birthdate').datepicker({
        format: 'yyyy-mm-dd',
        endDate: FromEndDate,
        autoclose: true
    });

    $("#adduser").validate({
        ignore: "",
        rules: userRules,
        messages: {
            name: {
                required: "<?php echo trans('labels.namerequired')?>"
            },
            email: {
                email: "<?php echo trans('labels.invalidemail') ?>"
            },
            password:{
                required: "<?php echo trans('labels.passwordrequired') ?>"
            },
            phone: {
                required: "<?php echo trans('labels.phonerequired') ?>",
                digits: "<?php echo trans('labels.digitsrequired') ?>",
                maxlength: "<?php echo trans('labels.phonelengthrequired') ?>",
                minlength: "<?php echo trans('labels.phonelengthrequired') ?>",
            },
            gender: {
                required: "<?php echo trans('labels.genderrequired') ?>"
            }

        },
        errorPlacement: function(error, element) {
            if (element.attr("name") == "gender" ){
                error.appendTo('#gender_error');
            }
            else
                error.insertAfter(element);
        }            
    });
    
});
</script>
@stop

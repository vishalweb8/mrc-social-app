@extends('Admin.Master')

@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.tag-editor.css') }}" />
<link rel='stylesheet' href='https://bootstrap-tagsinput.github.io/bootstrap-tagsinput/dist/bootstrap-tagsinput.css'>


<!-- Content Wrapper. Contains page content -->
<style type="text/css">
    .bootstrap-tagsinput .tag {
        margin-right: 2px;
        color: white !important;
        background-color: #4137ce;
        padding: .2em .6em .3em;
        font-size: 100%;
        font-weight: 700;
        vertical-align: baseline;
        border-radius: .25em;
    }
 
    #myUL {
        list-style-type: none;
        padding: 0;
        margin: 0;
        position: absolute;
        z-index: 1000;
        width: 100%;
    }

    #myUL li a{
        border: 1px solid #ddd;
        margin-top: -1px;
        /* Prevent double borders */
        background-color: #f6f6f6;
        padding: 7px;
        text-decoration: none;
        font-size: 15px;
        color: black;
        display: block
    }

    #myUL li a:hover:not(.header) {
        background-color: #eee;
    }
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.jobs')}}
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
                    <h3 class="box-title"><?php echo (isset($jobVacancy) && !empty($jobVacancy)) ? ' Edit ' : 'Add' ?> {{trans('labels.jobs')}}</h3>
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
                <form class="form-horizontal" method="post" action="{{route('jobs.store')}}" enctype="multipart/form-data" novalidate="novalidate">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{$jobVacancy->id ?? ''}}" />
                    <?php
                    if (old('location_id')){
                        $location_id = old('location_id');
                        $displayLocation = $autoLocation->city.'('.$autoLocation->pincode.')';
                    }  
                    elseif (isset($jobVacancy) && !empty($jobVacancy->location_id))
                    {
                        $location_id = $jobVacancy->location_id;
                        $displayLocation = $autoLocation->city.'('.$autoLocation->pincode.')';
                    }
                    else{
                        $location_id = '';
                        $displayLocation="";
                    }
                        
                    ?>
                    <input type="hidden" name="location_id" id="location_id" value="{{$location_id}}" />

                    <div class="box-body">
                        <div class="form-group">
                            <div class="col-md-4 ">
                                <?php
                                if (old('application_id'))
                                    $application_id = old('application_id');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->application_id))
                                    $application_id = $jobVacancy->application_id;
                                else
                                    $application_id = '';
                                ?>
                                <label for="application_id" class="control-label">{{ trans('labels.application') }}<span class="star_red">*</span></label>
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
                            <div class="col-sm-4">
                            
                                <label for="title" class="control-label"> Enter Pincode<span class="star_red">*</span></label>
                                <input class="location_name form-control" value="{{$displayLocation}}" name="location_name" type="text">
                                <ul id='myUL'></ul>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('title'))
                                    $title = old('title');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->title))
                                    $title = $jobVacancy->title;
                                else
                                    $title = '';
                                ?>
                                <label for="title" class="control-label">{{trans('labels.title')}}<span class="star_red">*</span></label>
                                <div>
                                    <input type="text" class="form-control" id="title" name="title" value="{{$title}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <?php
                                if (old('description'))
                                    $description = old('description');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->description))
                                    $description = $jobVacancy->description;
                                else
                                    $description = '';
                                ?>
                                <label for="description" class="control-label">{{trans('labels.description')}}<span class="star_red">*</span></label>
                                <div>
                                    <textarea type="text" class="form-control" id="description" name="description" >{{$description}}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-4 ">
                                <?php
                                if (old('external_link'))
                                    $external_link = old('external_link');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->external_link))
                                    $external_link = $jobVacancy->external_link;
                                else
                                    $external_link = '';
                                ?>
                                <label for="external_link" class="control-label">{{trans('labels.external_link')}}</label>
                                <div>
                                    <input type="text" class="form-control" id="external_link" name="external_link" value="{{$external_link}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('qualification'))
                                    $qualification = old('qualification');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->qualification))
                                    $qualification = $jobVacancy->qualification;
                                else
                                    $qualification = '';
                                ?>
                                <label for="qualification" class="control-label">{{trans('labels.qualification')}}<span class="star_red">*</span></label>
                                <div>
                                    <input type="text" placeholder="BCA,MCA" class="form-control" id="qualification" name="qualification" value="{{$qualification}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('experience'))
                                    $experience = old('experience');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->experience))
                                    $experience = $jobVacancy->experience;
                                else
                                    $experience = '';
                                ?>
                                <label for="experience" class="control-label">{{trans('labels.experience')}} <span class="star_red">*</span></label>
                                <div>
                                    <input type="number" class="form-control" id="experience" name="experience" value="{{$experience}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-4 ">
                                <?php
                                if (old('workplace_type'))
                                    $workplace_type = old('workplace_type');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->workplace_type))
                                    $workplace_type = $jobVacancy->workplace_type;
                                else
                                    $workplace_type = '';
                                ?>
                                <label for="workplace_type" class="control-label">{{trans('labels.workplace_type')}} <span class="star_red">*</span></label>
                                <div>
                                    <select name="workplace_type" data="" class="form-control">
                                        <option value="">Select {{trans('labels.workplace_type')}}</option>

                                        <option class="type_parent_cat cat_type" value="On-Site" {{($workplace_type == "On-Site")? 'selected' : ''}}>On-Site</option>
                                        <option class="type_parent_cat cat_type" value="On-Site" {{($workplace_type == "Remote")? 'selected' : ''}}>Remote</option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('employment_type'))
                                    $employment_type = old('employment_type');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->employment_type))
                                    $employment_type = $jobVacancy->employment_type;
                                else
                                    $employment_type = '';
                                ?>
                                <label for="type" class="control-label">{{trans('labels.employment_type')}} <span class="star_red">*</span></label>
                                <div>
                                    <select name="employment_type" data="" class="form-control">
                                        <option value="">Select {{trans('labels.employment_type')}}</option>
                                        <option class="type_parent_cat cat_type" value="Full-Time" {{($employment_type == "Full-Time")? 'selected' : ''}}>Full-Time </option>
                                        <option class="type_parent_cat cat_type" value="Part-Time" {{($employment_type == "Part-Time")? 'selected' : ''}}>Part-Time </option>
                                        <option class="type_parent_cat cat_type" value="Contract" {{($employment_type == "Contract")? 'selected' : ''}}>Contract </option>
                                        <option class="type_parent_cat cat_type" value="Temporary" {{($employment_type == "Temporary")? 'selected' : ''}}>Temporary </option>
                                        <option class="type_parent_cat cat_type" value="Volunteer" {{($employment_type == "Volunteer")? 'selected' : ''}}>Volunteer </option>
                                        <option class="type_parent_cat cat_type" value="Internship" {{($employment_type == "Internship")? 'selected' : ''}}>Internship </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('company_name'))
                                    $company_name = old('company_name');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->company_name))
                                    $company_name = $jobVacancy->company_name;
                                else
                                    $company_name = '';
                                ?>
                                <label for="company_name" class="control-label">{{trans('labels.company_name')}} <span class="star_red">*</span></label>
                                <div>
                                    <input company_name="text" class="form-control" id="company_name" name="company_name" value="{{$company_name}}">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <?php
                                if (old('skills'))
                                    $skills = old('skills');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->skills))
                                    $skills = $jobVacancy->skills;
                                else
                                    $skills = ''; 
                                ?>
                                <label for="skills" class="control-label">{{trans('labels.skills')}}<span class="star_red">*</span></label>
                                <div>
                                    <input type="text" name="skills" class="form-control" value="{{ $skills }}" data-role="tagsinput" />
                                </div>
                            </div>

                            <div class="col-md-4 ">
                                <?php
                                if (old('image_url'))
                                    $image_url = old('image_url');
                                elseif (isset($jobVacancy) && !empty($jobVacancy->image_url))
                                    $image_url = $jobVacancy->image_url;
                                else
                                    $image_url = '';
                                ?>
                                <label for="name" class="control-label">{{trans('labels.image')}}</label>
                                <div>
                                    <input type="file" id="image_url" name="image_url">
                                </div>
                            </div>
                            <div class="col-md-4" style="margin-top: 10px;">
                                @if(isset($jobVacancy) && !empty($jobVacancy))
                                    @if($jobVacancy->image_url != '')
                                        <div class="form-group" id="icon">
                                            <label for="media_images" class="col-sm-2 control-label">&nbsp;</label>
                                            <div class="col-sm-8">
                                                @if(Storage::disk(config('constant.DISK'))->exists(Config::get('constant.JOB_IMAGE_PATH').$jobVacancy->image_url)) 
                                                    <img src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.JOB_IMAGE_PATH').$jobVacancy->image_url) }}" width="50" height="50"/>
                                                @endif                                                
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="box-footer">
                            <div class="pull-right">
                                <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                                <a class="btn btn-default" href="{{ url('/admin/jobs') }}">{{trans('labels.cancelbtn')}}</a>
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
<script src='https://bootstrap-tagsinput.github.io/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
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
<script>
    $(function() {
        $('input').on('change', function(event) {

            var $element = $(event.target);
            var $container = $element.closest('.example');

            if (!$element.data('tagsinput'))
                return;

            var val = $element.val();
            if (val === null)
                val = "null";
            var items = $element.tagsinput('items');

            $('code', $('pre.val', $container)).html(($.isArray(val) ? JSON.stringify(val) : "\"" + val.replace('"', '\\"') + "\""));
            $('code', $('pre.items', $container)).html(JSON.stringify($element.tagsinput('items')));


        }).trigger('change');
    });
</script>
<script type="text/javascript"> 
    $(document).on("click",".setLocation",function() {
        var location_id = ($(this).attr('location_id'))
        var displayValue = ($(this).attr('displayValue'))
        $('#myUL').hide(1000);
        $('#location_id').val(location_id);
        $('.location_name').val(displayValue)   
    })
    $('.location_name').on('blur', function(event) {
        $('#myUL').hide(1000);
        // var length = $("#location_id").val().length;
        // if(length>0){ 
        //     $(".location_name").val('<?php echo $displayLocation;?>');
        // }
        // else{
        //     $("#location_id").val('') 
        // }
    });  
    $('.location_name').on('keyup', function(event) {
        $('#myUL').show()
        var path = "{{ url('admin/autocomplete') }}";
        var length = $(".location_name").val().length;
        var query = $(".location_name").val();
        if (length > 4) {
            $.ajax({
                type: 'get',
                url: path,
                data: {
                    pincode: query
                },
                success: function(data) {
                    var html = "";
                    $.each(data, function(key, value) {
                        html += "<li><a class='setLocation' location_id='"+value.id +"' displayValue='" + value.city + '('+ value.pincode +')'+"'>" + value.city + '('+ value.pincode +")</a></li> ";
                    });
                    html += ""; 
                    $('#myUL').html(html);
                }
            });

            // $('input.location_id').typeahead({
            //     source:  function (query, process) {
            //     return $.get(path, { pincode: query }, function (data) {
            //             return process(data);
            //         });
            //     }
            // });
        }
    });
</script>
@stop
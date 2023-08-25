@extends('Admin.Master')

@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.tag-editor.css') }}" />
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css">
<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.lblcategorymanagement')}}
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
                    <h3 class="box-title"><?php echo (isset($data) && !empty($data)) ? ' Edit ' : 'Add' ?> {{trans('labels.lblcategory')}}</h3>
                </div>  <!-- .box-header -->
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
                <form id="addCategoryManagement" class="form-horizontal" method="post" action="{{ url('/admin/category/savecategory') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($data) && !empty($data)) ? $data->id : '0' ?>">
                    <input type="hidden" name="hidden_cat_logo" value="<?php echo (isset($data) && !empty($data)) ? $data->cat_logo : '' ?>">
                    <input type="hidden" name="hidden_banner_img" value="<?php echo (isset($data) && !empty($data)) ? $data->banner_img : NULL ?>">
                    <div class="box-body">

                        <div class="form-group">
                            <?php
                                if (old('name'))
                                    $name = old('name');
                                elseif (isset($data) && !empty ($data->name))
                                    $name = $data->name;
                                else
                                    $name = '';
                            ?>
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.lblcategoryname')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="name" name="name" value="{{$name}}">
                            </div>
                        </div>
<!--                        <div class="form-group"> -->
                            <?php
                        //      if (old('category_slug'))
                        //        $category_slug = old('category_slug');
                        //       elseif (isset ($data) && !empty ($data->category_slug))
                        //        $category_slug = $data->category_slug;
                        //    else
                        //        $category_slug = ''; // {{$category_slug}}
                            ?>
                    <!--    <label for="category_slug" class="col-sm-2 control-label">{{trans('labels.categoryslug')}}</label>
                            <div class="col-sm-8">
                                <input type="text" readonly="true" class="form-control" id="category_slug" name="category_slug" placeholder="{{trans('labels.categoryslug')}}" value="">
                            </div>
                        </div>-->

                        <div class="form-group">
                        <?php
                            if (old('cat_logo'))
                                $cat_logo = old('cat_logo');
                            elseif (isset($data) && !empty ($data->cat_logo))
                                $cat_logo = $data->cat_logo;
                            else
                                $cat_logo = '';
                        ?>
                            <label for="cat_logo" class="col-sm-2 control-label">{{trans('labels.catlogo')}}<span class="star_red">*</span></label>
                            <div class="col-sm-5">
                                <input type="file" class="form-control" onchange="readURL2(this);" id="cat_logo" name="cat_logo">
                            </div>
                            <div class="col-sm-3">
                                @if(!empty($cat_logo) && $cat_logo != '')
                                    @if($cat_logo != '' && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$cat_logo))
                                        <img src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$cat_logo) }}" width="50" height="50" class="report-image img-thumbnail"/>
                                    @else
                                        <img src="{{ url('images/default.png') }}" width="50" height="50"/>
                                    @endif
                                @else
                                        <img src="{{ asset(Config::get('constant.DEFAULT_IMAGE')) }}" class="report-image img-thumbnail" alt="{{$cat_logo}}" height="{{Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_HEIGHT')}}" width="{{Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_WIDTH')}}">
                                @endif
                            </div>
                            <div class="form-group" id="preview_photo_img2" style="display:none;">
                                <label for="addphotos" class="col-sm-2 control-label"></label>
                                <div class="" id="preview_photo"></div>
                            </div>
                        </div>

                        <div class="form-group">
                        <?php
                            if (old('banner_img'))
                                $banner_img = old('banner_img');
                            elseif (isset ($data) && !empty ($data->banner_img))
                                $banner_img = $data->banner_img;
                            else
                                $banner_img = '';
                        ?>
                            <label for="banner_img" class="col-sm-2 control-label">{{trans('labels.bannerimage')}}<span class="star_red">*</span></label>
                            <div class="col-sm-5">
                                <input type="file" class="form-control" id="banner_img" name="banner_img">
                            </div>
                            <div class="col-sm-3">
                                @if(!empty($banner_img) && $banner_img != '')
                                    @if(file_exists(Config::get('constant.CATEGORY_BANNER_ORIGINAL_IMAGE_PATH').$banner_img))
                                        <img src="{{ asset(Config::get('constant.CATEGORY_BANNER_ORIGINAL_IMAGE_PATH').$banner_img) }}" class="report-image img-thumbnail" alt="{{$banner_img}}" height="{{Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_HEIGHT')}}" width="{{Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_WIDTH')}}" />
                                    @endif

                                    @if($banner_img != '' && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_BANNER_ORIGINAL_IMAGE_PATH').$banner_img))
                                        <img src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_BANNER_ORIGINAL_IMAGE_PATH').$banner_img) }}" class="report-image img-thumbnail" alt="{{$banner_img}}" height="50" width="100"/>
                                    @else

                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('service_type'))
                                $service_type = old('service_type');
                            elseif (isset($data))
                                $service_type = $data->service_type;
                            else
                                $service_type = '';
                            ?>
                            <label for="service_type" class="col-sm-2 control-label">Service Type?</label>
                            <div class="col-sm-8">
                                <input type="checkbox" class="check_box_set" name="service_type" {{($service_type == 1)?'checked':''}} value="1">
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                                if (old('metatags'))
                                    $metatags = old('metatags');
                                elseif (isset($data) && !empty ($data->metatags))
                                    $metatags = $data->metatags;
                                else
                                    $metatags = '';
                            ?>
                            <label for="metatags" class="col-sm-2 control-label">{{trans('labels.metatags')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="metatags" name="metatags" value="{{$metatags}}">
                            </div>
                        </div>

                        @if(isset($data) && !empty($data) && $data->id != '')
                        <hr/>
                        <div class="form-group">
                            <label for="metatags" class="col-sm-2 control-label">Move category?</label>
                            <div class="col-sm-8">
                                <input type="radio" class="check_box_set" value="1" name="action"> Yes
                                <input type="radio" class="check_box_set" value="0" name="action"> No
                            </div>
                        </div>
                        <div class="form-group" style="display: none;" id="parent">
                            <label for="metatags" class="col-sm-2 control-label">Category</label>
                            <div class="col-sm-8">
                                <select name="parent" data="" class="form-control select2" id="parent_id">
                                    <option value="">Select Category</option>
                                    <option value="0">Root</option>
                                    @forelse($categories as $category)
                                        <option value="{{$category->id}}" <?php if(in_array($category->id, $childHirarchyCategory)){ echo 'disabled'; } ?>>{{$category->name}} </option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        @endif

                    </div>

                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                            <a class="btn btn-default" href="{{ url('/admin/categories') }}">{{trans('labels.cancelbtn')}}</a>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
<!-- Include tags in input box -->
<script src="{{ asset('js/jquery.caret.min.js') }}"></script>
<script src="{{ asset('js/jquery.tag-editor.js') }}"></script>

<script type="text/javascript">
    $('input[type="radio"]').on('click', function(e) {
        if($( "input[type='radio']:checked" ).val() == 1)
        {
            $('#parent').show();
        }
        if($( "input[type='radio']:checked" ).val() == 0)
        {
            $('#parent').hide();
        }
    });
    $('#metatags').tagEditor({
        placeholder: 'Enter Metatags ...',
    });
    $('.select2').select2();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function readURL2(input)
    {
//      $('#preview_photo_img2').show();
        var image_length = input.files.length;
        for (var i = 0; i < image_length; i++)
        {
            if (input.files && input.files[i])
            {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#preview_photo').append('<div class="col-sm-2 image_caption"><img width="60" height="60" src="' + e.target.result + '"/></div>');
                }
                reader.readAsDataURL(input.files[i]);
            }
        }
    }

</script>

<script type="text/javascript">
   <?php if (!isset($data) && empty($data)){ ?>
//    $('#name').keyup(function () {
//        var str = $(this).val();
//        str = $.trim(str);
//        str = str.replace(/[^a-zA-Z0-9\s]/g, "");
//        str = str.toLowerCase();
//        str = str.replace(/\s/g, '-');
//        $('#category_slug').val(str);
//    });
    
    <?php } ?>
</script>

<script type="text/javascript">
    var validateRules = {
        name: {
            required: true
//          letterswithbasicpunc: true
        }
    };
    $("#addCategoryManagement").validate({
        ignore: "",
        rules: validateRules,
        messages: {
            name:{
                required: "Category Name is required"
            },
        }
    });
</script>

<!--<script type="text/javascript">

    $(document).ready(function () {
        fetchCetegory();
    });

    function fetchCetegory()
    {
        var cboxArray = [];
        if ($(this).is(':checked'))
        {
            $(':checked').each(function() {
                if($(this).attr("name") !== undefined)
                {
                    cboxArray.push($(this).attr("name"));
                }
            });
        }
        else
        {
            $(':checked').each(function() {
                if($(this).attr("name") !== undefined)
                {
                    cboxArray.push($(this).attr("name"));
                }
            });
            var removeItem = $(this).attr("name");
            cboxArray = $.grep(cboxArray, function(value) {
                return value != removeItem;
            });
        }
        console.log(cboxArray);
        $.ajax({
            type: 'post',
            url: '{{ url("admin/getParentCategory") }}',
            data: {
                checkboxArray: cboxArray,
                editId:'<?php // echo (isset($data->id) && !empty($data->id)) ? $data->id : '0' ?>'
            },
            success: function (response)
            {
                var $response = $(response);
                var parentCategoryData = $response.filter('#parentCategoryCollection').html();
                document.getElementById("parentCategoryCollection").innerHTML = parentCategoryData;
            }
        });
    }

</script>-->

@stop

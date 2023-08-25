@extends('Admin.Master')

@section('header') 
<link type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css">

@endsection

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        &nbsp;
        Edit / [{{ $data->id }}] {{$data->name}} / Advertisement
        <a class="pull-left" href="{{ url('/admin/advertisement') }}" title="Back">
            <i class="fa fa-arrow-circle-left" aria-hidden="true"></i>
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('admin/users') }}"><i class="fa fa-users"></i> Users</a></li>
        <li class="active">{{$userDetail->name}}</li>
        <li class="active">{{ $data->name .' '}} Advertisement</li>
    </ol>
</section>

<!-- Main content -->
<section class="content">
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
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
        </div>
    </div>
    <div class="row">
        <!-- right column -->
        <div class="col-md-6">
            <!-- Horizontal Form -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $data->id }} | Advertisement Information</h3>
                </div><!-- /.box-header -->
                <form id="frmAdvertisement" class="form-horizontal" method="post" action="{{ url('/admin/advertisement/saveAdvertisementInfo') }}"
                    enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($data) && !empty($data)) ? $data->id : '0' ?>">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">

                    <div class="box-body">
                        <div class="form-group">
                            <?php
                                if (old('ads_type'))
                                    $ads_type = old('ads_type');
                                elseif (isset($data))
                                    $ads_type = $data->ads_type;
                                else
                                    $ads_type = '';
                                ?>
                            <label for="approved" class="col-sm-2 control-label">Ads.Type<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <select name="ads_type" class="form-control" style="cursor: pointer;"
                                    {{(Auth::user()->agent_approved == 1) ? 'disabled' : ''  }}>
                                    <option value="0" {{($ads_type == 0)?'selected': ''}}>Buy</option>
                                    <option value="1" {{($ads_type == 1)?'selected': ''}}>Sell</option>
                                    <option value="2" {{($ads_type == 2)?'selected': ''}}>Service</option>
                                </select>
                            </div>
                        </div>
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
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{trans('labels.name')}}"
                                    value="{{$name}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('descriptions'))
                                $descriptions = old('descriptions');
                            elseif (isset($data))
                                $descriptions = $data->descriptions;
                            else
                                $descriptions = '';
                            ?>
                            <label for="descriptions" class="col-sm-2 control-label">{{trans('labels.description')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <textarea type="text" class="form-control" id="descriptions" name="descriptions" placeholder="{{trans('labels.description')}}">{{$descriptions}}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                            if (old('price'))
                                $price = old('price');
                            elseif (isset($data))
                                $price = ($data->price)?$data->price:'';
                            else
                                $price = '';
                            ?>
                            <label for="price" class="col-sm-2 control-label">Price</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" placeholder="Price" id="price" name="price"
                                    value="{{$price}}" maxlength="100" />
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('promoted'))
                                $promoted = old('promoted');
                            elseif (isset($data))
                                $promoted = $data->promoted;
                            else
                                $promoted = '';
                            ?>
                            <label for="promoted" class="col-sm-2 control-label">{{trans('labels.promoted')}}</label>
                            <div class="col-sm-8">
                                <input type="checkbox" class="check_box_set" name="promoted"
                                    {{($promoted == 1)?'checked':''}}
                                    {{(Auth::user()->agent_approved == 1) ? 'disabled' : ''  }}>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('sponsored'))
                                $sponsored = old('sponsored');
                            elseif (isset($data))
                                $sponsored = $data->sponsored;
                            else
                                $sponsored = '';
                            ?>
                            <label for="sponsored" class="col-sm-2 control-label">Sponsored</label>
                            <div class="col-sm-8">
                                <input type="checkbox" class="check_box_set" name="sponsored"
                                    {{($sponsored == 1)?'checked':''}}
                                    {{(Auth::user()->agent_approved == 1) ? 'disabled' : '' }}>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('approved'))
                                $approved = old('approved');
                            elseif (isset($data))
                                $approved = $data->approved;
                            else
                                $approved = '';
                            ?>
                            <label for="approved" class="col-sm-2 control-label">{{trans('labels.status')}}</label>
                            <div class="col-sm-8">
                                <select name="approved" class="form-control" style="cursor: pointer;"
                                    {{(Auth::user()->agent_approved == 1) ? 'disabled' : ''  }}>
                                    <option value="0" {{($approved == 0)?'selected': ''}}>Not approved</option>
                                    <option value="1" {{($approved == 1)?'selected': ''}}>Approved</option>
                                    <option value="2" {{($approved == 2)?'selected': ''}}>Rejected</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('is_closed'))
                                $is_closed = old('is_closed');
                            elseif (isset($data))
                                $is_closed = $data->is_closed;
                            else
                                $is_closed = '';
                            ?>
                            <label for="is_closed" class="col-sm-2 control-label">Ads. Status (Open/Closed):</label>
                            <div class="col-sm-8">
                                <select name="is_closed" class="form-control">
                                    <option value="0" {{ ($is_closed == 0) ? 'selected': ''}}>Open</option>
                                    <option value="1" {{ ($is_closed == 1) ? 'selected': ''}}>Closed</option>
                                </select>
                            </div>
                        </div>

                    </div><!-- /.box-body -->
                    @if (isset($data) && ($data->deleted_at == "" || $data->deleted_at == null))
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{$data->deleted_at}} {{trans('labels.savebtn')}}</button>
                        </div>
                    </div><!-- /.box-footer -->
                    @endif
                </form>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Video Links</h3>
                </div><!-- /.box-header -->
                <form id="addAdvertisementVideos" class="form-horizontal" method="post" action="{{ url('admin/advertisement/saveVideoLinks') }}"
                    enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($data) && !empty($data)) ? $data->id : '0' ?>">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">

                    <div class="box-body">
                        @if(isset($data) && isset($data->advertisementVideos) && !empty($data->advertisementVideos->toArray()))

                        @forelse($data->advertisementVideos->toArray() as $key=>$video)
                        <div class="form-group" id="video_link_{{$video['id']}}">
                            @if($key == 0)
                            <label for="video_link_title" class="col-sm-2 control-label">Video Link</label>
                            @else
                            <label for="video_link_title" class="col-sm-2 control-label"></label>
                            @endif
                            <div id='video_link_title'>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" placeholder="" id="" name="update_video_link_title[]" value="{{$video['video_link']}}">
                                    <input class="form-control" type="hidden" placeholder="" id="" name="update_video_link_id[]" value="{{$video['id']}}">
                                </div>
                                @if($key > -1)
                                <div class="col-sm-1">
                                    <button type="button" class="btn bg-purple" onclick="remove_video_link({{$video['id']}})">-</button>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        @endforelse
                        <div class="form-group">
                            <label for="video_link_title" class="col-sm-2 control-label"></label>
                            <div id='video_link_title'>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" placeholder="" id="" name="video_link[]" value="" />
                                </div>
                                <div class="col-sm-1">
                                    <button type="button" class="btn bg-purple add_video_link">+</button>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="form-group">
                            <label for="video_link_title" class="col-sm-2 control-label">{{trans('labels.title')}}</label>
                            <div id='video_link_title'>
                                <div class="col-sm-7">
                                    <input class="form-control" type="text" placeholder="" id="" name="video_link[]" value="" />
                                </div>
                                <div class="col-sm-1">
                                    <button type="button" class="btn bg-purple add_video_link">+</button>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div id="add_more_video_link"></div>
                        <div id="deleted_video_link"></div>
                    </div><!-- /.box-body -->
                    @if (isset($data) && ($data->deleted_at == "" || $data->deleted_at == null))
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                        </div>
                    </div><!-- /.box-footer -->
                    @endif
                </form>
            </div>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Ads Statistics</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <div class="row"><div class="col-md-12"></div></div>
                    <div class="row"><div class="col-md-12"><label>Visit Count:</label> {{ $data->visit_count}}</div></div>
                    <div class="row"><div class="col-md-12"><label>Interest Count:</label> {{ $data->interest_count}}</div></div>
                </div>
            </div>
            @if($dataInterest)
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Interest Showed By User</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-hover" id="tblAdvertisement"
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name / Phone</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataInterest as $interest)
                            <tr>
                                <td><img src="{{ $interest["image_url"]}}" style="width:100px" class="img-responsive" /></td>
                                <td>{{ $interest["name"] }}<br />{{ $interest["country_code"] .'-'. $interest["phone_number"] }}</td>
                                <td>{{ $interest["timestamp"] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        <!-- right column -->
        <div class="col-md-6">
            <!-- Horizontal Form -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        Category <span data-toggle="tooltip" data-original-title="Edit Category" class="glyphicon glyphicon-edit"
                            style="margin-left:10px;cursor: pointer;" id="editCategory"></span>
                    </h3>
                </div><!-- /.box-header -->
                <form id="addAdvertisementCategory" class="form-horizontal" method="post" action="{{ url('admin/advertisement/saveCategory') }}"
                    enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($data) && !empty($data)) ? $data->id : '0' ?>">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">

                    <?php
                        if (old('category_hierarchy'))
                            $category_hierarchy = old('category_hierarchy');
                        elseif (isset($data))
                            $category_hierarchy = $data->child_category_ids;
                        else
                            $category_hierarchy = '';
                    ?>

                    <div class="form-group">
                        <label for="category_id_display" class="col-sm-2 control-label">
                            {{trans('labels.lblcategories')}}
                        </label>
                        <div class="col-sm-8" style="margin-top:-12px;padding-bottom: 10px;" id="category_id_display">
                            @if($category_hierarchy != '')
                                <?php  $explodeCategories = explode(',',$data->child_category_ids); ?>
                                @foreach($explodeCategories as $category)
                                    @if($category > 0)
                                        <?php  $categories = Helpers::getCategoryReverseHierarchy($category); ?><br>
                                        @if(!empty($categories))
                                        <?php
                                            $tempArr = [];
                                            foreach($categories as $categoryTemp){
                                                $tempArr[] = '<span class="label label-success">'.$categoryTemp['name'].'</span>';
                                            }
                                            echo implode(" > ", array_reverse($tempArr));
                                        ?>
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div id="edit_cat" style="display:inline;">
                        <div class="form-group">
                            <?php
                            if (old('category_id'))
                                $category_id = old('category_id');
                            elseif (isset($data)){
                                $category_id = $data->child_category_ids;
                            } else {
                                $category_id = '';
                            }

                            $categorySelectedId = '';
                            if($data->category_hierarchy)
                            {
                                $categorySelectedId = substr($data->category_hierarchy, 0, 1); 
                            }
                            ?>
                            <label for="category_id" class="col-sm-2 control-label">{{(isset($data)) ? trans('labels.category') : trans('labels.category') }}</label>
                            <div class="col-sm-8">
                                <select name="parent_category" data="" class="form-control" onchange="return getParentSubCategory(this.value,1);">
                                    <option value="">Select {{trans('labels.parentcategory')}}</option>
                                    @forelse($parentCategories as $category)
                                        <option class="type_parent_cat cat_type" value="{{$category->id}}">
                                            {{$category->name}}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>

                        <div id="subcategory_0" class="subcategory_class" style="display: none;"></div>
                        <div id="subcategory_1" class="subcategory_class"></div>

                        <div class="form-group">
                            <label for="description" class="col-sm-2 control-label">&nbsp;</label>
                            <div id="selected_subcat" class="col-sm-8">
                                <?php 
                                    $pCategoryArray = $data->parent_category_ids;
                                    $explodePCategory = explode(',',$pCategoryArray);
                                ?>
                                @if($explodePCategory)
                                    @foreach($explodePCategory as $category)
                                    <input type="hidden" name="parent_category_id[]" value="{{$category}}" />
                                    @endforeach
                                @else
                                    <input type="hidden" name="parent_category_id[]" />
                                @endif
                                <?php 
                                    $categoryArray = $data->child_category_ids;
                                    $explodeCategory = explode(',',$categoryArray);
                                ?>
                                @if($explodeCategory)
                                    @foreach($explodeCategory as $category)
                                        <?php    
                                            $catId = $category;
                                            $categoryHierarchy = array_reverse(Helpers::getCategoryReverseHierarchy($catId));
                                        ?>
                                        @if(!empty($categoryHierarchy))
                                        <div>
                                            <input type="hidden" value="{{$catId}}" name="category_id[]" />
                                            <ol class="breadcrumb" style="margin-bottom: 5px;">
                                                @forelse($categoryHierarchy as $cat)
                                                <li class="active">{{$cat['name']}}</li>
                                                @empty
                                                @endforelse
                                                <span style="cursor: pointer;" class="pull-right badge bg-red" onclick="remove_subcat(this,{{$catId}});"><i class="fa fa-remove"></i></span>
                                            </ol>
                                        </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @if (isset($data) && ($data->deleted_at == "" || $data->deleted_at == null))
                        <div class="box-footer">
                            <div class="pull-right">
                                <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                            </div>
                        </div><!-- /.box-footer -->
                        @endif
                    </div>
                </form>
            </div>

            <!-- Horizontal Form -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        Contact Details
                    </h3>
                </div><!-- /.box-header -->
                <form id="adsContactInfo" class="form-horizontal" method="post" action="{{ url('admin/advertisement/saveContactInfo') }}"
                    enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($data) && !empty($data)) ? $data->id : '0' ?>">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">

                    <div class="box-body">
                        <div class="form-group">
                            <?php
                            if (old('address'))
                                $address = old('address');
                            elseif (isset($data))
                                $address = $data->address;
                            else
                                $address = '';
                            ?>
                            <label for="address" class="col-sm-2 control-label">{{trans('labels.address')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="address" placeholder="{{trans('labels.address')}}" value="{{$address}}" onFocus="initializeAutocomplete()" id="locality" />
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                                $hidden_latitude = "";
                                if (old('latitude'))
                                    $latitude = old('latitude');
                                elseif (isset($data))
                                    $hidden_latitude = $latitude = $data->latitude;
                                else
                                    $latitude = '';
                            ?>
                            <label for="latitude" class="col-sm-2 control-label">{{trans('labels.latitude')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="latitude" placeholder="{{trans('labels.latitude')}}"
                                    value="{{$latitude}}" id="latitude" />
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                                $hidden_longitude = "";
                                if (old('longitude'))
                                    $longitude = old('longitude');
                                elseif (isset($data))
                                    $hidden_longitude = $longitude = $data->longitude;
                                else
                                    $longitude = '';
                            ?>
                            <label for="longitude" class="col-sm-2 control-label">{{trans('labels.longitude')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="longitude" placeholder="{{trans('labels.longitude')}}"
                                    value="{{$longitude}}" id="longitude" />
                            </div>
                        </div>

                        <input type="hidden" name="hidden_latitude" id="hidden_latitude" value="{{$hidden_latitude}}" />
                        <input type="hidden" name="hidden_longitude" id="hidden_longitude" value="{{$hidden_longitude}}" />

                        <div class="form-group">
                            <?php
                            if (old('street_address'))
                                $street_address = old('street_address');
                            elseif (isset($data))
                                $street_address = $data->street_address;
                            else
                                $street_address = '';
                            ?>
                            <label for="street_address" class="col-sm-2 control-label">{{trans('labels.streetaddress')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="street_address" placeholder="{{trans('labels.streetaddress')}}"
                                    value="{{$street_address}}" id="street_address" />
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
                                <select name="country" id="address_country" class="form-control select2">
                                    <option value="">Select {{trans('labels.country')}}</option>
                                    @forelse($countries as $con)
                                    <option class="type_parent_cat cat_type" value="{{$con->id}}"
                                        {{($country == $con->id)?'selected': ''}}>
                                        {{$con->name}}
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
                                <?php $states = Helpers::getStates(); ?>
                                <select name="state" id="address_state" class="form-control select2">
                                    <option value="">Select {{trans('labels.state')}}</option>
                                    @forelse($states as $st)
									@if($country == $st->country_id )
                                    <option class="type_parent_cat cat_type" value="{{$st->id}}"
                                        {{($state == $st->id)?'selected':''}}>
                                        {{$st->name}}
                                    </option>
									@endif
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
							
                                <?php $cities = Helpers::getCities();
									//echo "<pre>"; print_r($cities);
								?>
								 
                                <select id="address_city" name="city" class="form-control select2">
                                    <option value="">Select {{trans('labels.city')}}</option>
									
                                    @forelse($cities as $ct)
									
									@if($state == $ct->state_id )
                                    <option class="type_parent_cat cat_type" value="{{$ct->name}}"
                                        {{($city == $ct->name)?'selected':''}}>
                                        {{$ct->name}}
                                    </option>
									@endif
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php
                            if (old('pincode'))
                                $pincode = old('pincode');
                            elseif (isset($data))
                                $pincode = $data->pincode;
                            else
                                $pincode = '';
                            ?>
                            <label for="pincode" class="col-sm-2 control-label">{{trans('labels.pincode')}}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="pincode" placeholder="{{trans('labels.pincode')}}"
                                    value="{{$pincode}}" id="address_pincode" />
                            </div>
                        </div>
                    </div><!-- /.box-body -->
                    @if (isset($data) && ($data->deleted_at == "" || $data->deleted_at == null))
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                        </div>
                    </div><!-- /.box-footer -->
                    @endif
                </form>
            </div>
        </div>
    </div>
</section><!-- /.content -->
@stop

@section('script')
<script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
<script src="//maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyC0GOPX5KCBWMktECRDFUXODd2WSNXFae4"></script>
<!-- Include tags in input box -->
<script src="{{ asset('js/jquery.caret.min.js') }}"></script>
<!-- Include tags in input box -->
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>

<script type="text/javascript">

$(function() {
    $("#address_country").change(function() {
			var selected_country =  $('option:selected', this).val();
			// alert(selected_country);
		var token = '<?php echo csrf_token() ?>';
        $.ajax({
            headers: { 'X-CSRF-TOKEN': token },
            type: "POST",
            url: "{{url('/admin/getStateList')}}",
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
            url: "{{url('/admin/getCityList')}}",
            data: {selected_state: selected_state},
            success: function( data ) {				
				$('#address_city').html(data);
				//alert(data);              
            }
        });		
			
		});
	});
    function initializeAutocomplete() {
        var input = document.getElementById('locality');
        var options = {}

        var autocomplete = new google.maps.places.Autocomplete(input, options);

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            console.log(place);
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();
            var placeId = place.place_id;
            // to set city name, using the locality param
            var componentForm = {
                locality: 'short_name',
            };
            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (componentForm[addressType]) {
                    var val = place.address_components[i][componentForm[addressType]];
                }
                var addr = place.address_components[i];
                var getCountry;
                if (addr.types[0] == 'country') {
                    getCountry = addr.long_name;
                }
                var getState;
                if (addr.types[0] == 'administrative_area_level_1') {
                    getState = addr.long_name;
                }
                var getPostalCode;
                if (addr.types[0] == 'postal_code') {
                    getPostalCode = addr.long_name;
                }
                var getLocality;
                if (addr.types[0] == 'locality') {
                    getLocality = addr.long_name;
                }
                var getStreetAddress;
                if (addr.types[0] == 'route') {
                    getStreetAddress = addr.long_name;
                }

            }
            document.getElementById("latitude").value = lat;
            document.getElementById("longitude").value = lng;
        });
    }

    $(document).ready(function () {
        $('.select2').select2();

        $.validator.addMethod('filesize', function (value, element, param) {
            return this.optional(element) || (element.files[0].size <= param)
        }, 'File size must be less than 5 MB');

        var adsRules = {
            name: {
                required: true
            },
            descriptions: {
                required: true
            },
            'image_name[]': {
                extension: "jpeg|jpg|bmp|png",
                filesize: 5000000 // 5 mb
            },
            business_logo: {
                extension: "jpeg|jpg|bmp|png",
                filesize: 5000000 // 5 mb
            }
        };

        $("input.ipfile").each(function () {
            $(this).rules("add", {
                required: true,
                accept: "jpg|jpeg"
            });
        });

        $("#frmAdvertisement").validate({
            ignore: "",
            rules: adsRules,
            messages: {
                name: {
                    required: "<?php echo trans('labels.namerequired')?>"
                },
                descriptions: {
                    required: "<?php echo trans('labels.descriptionrequired')?>"
                }
            }
        });
    });

    function getParentSubCategory(categoryId, level) {
        var categoryArray = [categoryId];

        var token = '<?php echo csrf_token() ?>';
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': token
            },
            type: "POST",
            url: "{{url('/admin/search/subcategory')}}",
            data: {
                categoryIds: categoryArray,
                level: level
            },
            success: function (data) {
                if (level == 1) {
                    $('#subcategory_0').html(data);
                    $('#subcategory_1').html('');
                    $('#business_meta').show();
                }

                if (data) {
                    $('#subcategory_1').html(data);
                    $('#subcategory_0').hide();

                } else {
                    $('#subcategory_0').show();
                    $('#subcategory_1').html('<input type="hidden" name="category_id[]" value="' + categoryId + '"/>' +
                        '<span style="margin-left: 185px !important;">No Subcategory Found</span>');
                    $('#subcategory_1').append('');
                }

            }
        });
    }

    function addCategotyHierarchy(catId) {
        var flag = 0;
        $('input[name^="category_id"]').each(function () {
            if ($(this).val() == catId) {
                alert('Category is already selected ');
                flag = 1;
            }
        });
        
        if (flag == 0) {
            var token = '<?php echo csrf_token() ?>';
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': token
                },
                type: "POST",
                url: "{{url('/admin/search/addCategotyHierarchy')}}",
                data: {
                    catId: catId
                },
                success: function (data) {
                    parent_category_id = document.getElementsByName('categoryId').value;
                    data = $(data).first('div').append('<input type="hidden" name="parent_category_id[]" value="' + parent_category_id + '" />');
                    $('#selected_subcat').append(data);
                    $('#subcategory_0').show();
                    $('#subcategory_1').html('');
                }
            });
        }
    }

    function remove_subcat(catRemove, catId) {
        $(catRemove).parent().parent().remove();
        var token = '<?php echo csrf_token() ?>';
    }

    function addTagsToBusiness(selectval) {
        $('#metatags').tagEditor('addTag', selectval.value);
    }

    function deleteBusinessImage(imageId) {
        var x = confirm("Are you sure you want to delete?");
        if (x) {
            var token = '<?php echo csrf_token() ?>';
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': token
                },
                type: "POST",
                url: "{{url('/admin/advertisement/removeImage')}}",
                data: {
                    businessImageId: imageId
                },
                success: function (data) {
                    $('#advertisement_img_' + imageId).remove();
                }
            });
        } else
            return false;
    }

    $('.add_video_link').click(function () {
        var html =
            '<div class="form-group"><label for="video_link_title" class="col-sm-2 control-label"></label><div id="video_link_title"><div class="col-sm-7"><input  class="form-control" type="text" placeholder=""  id="" name="video_link[]" value=""></div><div class="col-sm-1"><button type="button" class="btn bg-purple remove_video_link" onClick="delete_video_link(this)">-</button></div></div></div>';
        $('#add_more_video_link').append(html);
    });

    function remove_video_link(id) {
        $('#video_link_' + id).remove()
        $('#deleted_video_link').append('<input type="hidden" name="deleted_video_link[]" value="' + id + '">');
    }

    function delete_video_link(val) {
        $(val).parent().parent().parent().remove();
    }

    $('#editCategory').click(function () {
        $('#edit_cat').toggle();
    })
</script>
@stop

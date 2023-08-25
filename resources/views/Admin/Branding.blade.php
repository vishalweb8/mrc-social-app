@extends('Admin.Master')

@section('header')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.branding')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <form id="addbrandingManagement" class="form-horizontal" method="post" action="{{ url('/admin/savebranding/') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($data) && !empty($data)) ? $data->id : '0' ?>">
                    @can(config('perm.addBranding'))
                    <div class="box-body">
                        <div class="form-group">
                            <label for="country_id" class="col-sm-2 control-label">
                                {{ trans('labels.type') }}
                            </label>
                            <div class="col-sm-6">
                                <select name="type" class="form-control" id="filetype">
                                    <option value="">Select type</option>
                                    <option value="1">Image</option>
                                    <option value="2">Video</option>
                                    <option value="3">Text</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="display: none;" id="image-part">
                            <label for="image" class="col-sm-2 control-label">
                                {{ trans('labels.image') }}
                            </label>
                            <div class="col-sm-6">
                                <input type="file" name="image" style="margin: 0px; width: 520px; height: 48px;"/>
                            </div>
                        </div>
                        <div class="form-group" style="display: none;" id="video-part">
                            <label for="video" class="col-sm-2 control-label">
                                {{ trans('labels.video') }}
                            </label>
                            <div class="col-sm-6">
                                <input type="text" name="video" class="form-control" placeholder="youtube url" />
                            </div>
                        </div>
                        <div class="form-group" style="display: none;" id="text-part">
                            <label for="text" class="col-sm-2 control-label">
                                {{ trans('labels.text') }}
                            </label>
                            <div class="col-sm-6">
                                <textarea name="text" cols="83" rows="2"></textarea> 
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="business_id" class="col-sm-2 control-label">
                                {{ trans('labels.business') }}
                            </label>
                            <div class="col-sm-6">
                                <select class="form-control" id="business_id" name="business_id">
                                    <option value="">Select Business</option>
                                </select>
                            </div>
                        </div>
						<div class="form-group">
                            <label for="page_name" class="col-sm-2 control-label">
                                {{ trans('labels.page_name') }} <a href='#' data-html="true" data-toggle="popover"  data-trigger="focus"  title="Pages" data-content="<h4>Use following pages</h4><ol>	
                                    <li>advertise-inquiry </li>
                                    <li>create-business-inquiry </li>
                                    <li>contact-us </li>
                                    <li>marketplace </li>
                                    <li>business-profile </li>
                                    <li>donate </li>
                                    <li>category-list?category_id=2 </li>
                                    <li>business-details?business_id=122 </li>
                                    <li>search?city=test&searchtext=doctors </li>
                                </ol>">(Note)</a>

                            </label>
                            <div class="col-sm-6">
                                <input type="text" name="page_name" class="form-control" placeholder="Page Name" />
                            </div>
                            <div>
                            
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.savebtn')}}</button>
                        </div>
                    </div><!-- /.box-footer -->
                    @endcan
                    <div class="box-footer">
                        @if(!empty($brandingDetail))
                            <div class="row">
                                @if($brandingDetail->type == 1)
                                    @if(file_exists('images/branding_image.png'))
                                        <label for="country_id" class="col-sm-2 control-label">
                                            Image url
                                        </label>
                                        <div class="col-sm-8">
                                            <image src="{{url('images/branding_image.png')}}" hight="30" width="30"/>
                                            {{url('images/branding_image.png')}}
                                            @can(config('perm.deleteBranding'))
                                            <a onclick="return confirm('Are you sure you want to delete ?')" href="{{ url('/admin/deletebranding') }}">
                                                <span data-toggle="tooltip" data-original-title="Delete" class='glyphicon glyphicon-remove'></span>
                                            </a>
                                            @endcan
                                        </div>
                                    @endif
                                @elseif($brandingDetail->type == 2)
                                    <label for="country_id" class="col-sm-2 control-label">
                                            Video url
                                    </label>
                                    <div class="col-sm-8">
                                        {{$brandingDetail->name}}
                                        @can(config('perm.deleteBranding'))
                                        <a onclick="return confirm('Are you sure you want to delete ?')" href="{{ url('/admin/deletebranding') }}">
                                            <span data-toggle="tooltip" data-original-title="Delete" class='glyphicon glyphicon-remove'></span>
                                        </a>
                                        @endcan
                                    </div>
                                @else
                                    <label for="country_id" class="col-sm-2 control-label">
                                            Text
                                    </label>
                                    <div class="col-sm-8">
                                        {{$brandingDetail->name}}
                                        @can(config('perm.deleteBranding'))
                                        <a onclick="return confirm('Are you sure you want to delete ?')" href="{{ url('/admin/deletebranding') }}">
                                            <span data-toggle="tooltip" data-original-title="Delete" class='glyphicon glyphicon-remove'></span>
                                        </a>
                                        @endcan
                                    </div>
                                @endif
                            </div>
                            @if($brandingDetail->business)
                                <div class="row">
                                    <label for="country_id" class="col-sm-2 control-label">
                                        Business Name
                                    </label>
                                    <div class="col-sm-8">
                                        {{$brandingDetail->business->name}}
                                    </div>
                                </div>
                            @endif
							@if($brandingDetail->page_name)
                                <div class="row">
                                    <label for="label" class="col-sm-2 control-label">
                                        Page Name
                                    </label>
                                    <div class="col-sm-8">
                                        {{$brandingDetail->page_name}}
                                    </div>
                                </div>
                            @endif
                        @endif

                        
                    </div><!-- /.box-footer -->
                </form>
                @can(config('perm.createGroupBranding'))
                <form class="form-horizontal" action="{{ url('/admin/branding/') }}" enctype="multipart/form-data"> 
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                
                <div class="content">
                    <div class="row"> 
                        <label for="category" class="col-sm-2 control-label">Search</label>
                    
                        <?php
                        // print_r($postData);die();
                            if (old('name'))
                                $Searchname = old('name');
                            elseif (isset($postData['name']))
                                $Searchname = $postData['name'];
                            else
                                $Searchname = '';
                            ?>
                     <div class="col-sm-2">
                        <input type="text" name="name" value="{{$Searchname}}" class="form-control" placeholder="Search name" />
                     </div>
                     <div class="col-sm-2">
                        <select name="category" id="category" class="form-control select2">
                            
                            <option value="">Choose category </option>

                          @foreach($categories  as $cat) 

                            <option value="{{$cat->id}}" {{(isset($postData['category']) && $postData['category'] == $cat->id)?'selected':''}}>{{$cat->name}} </option>
                          @endforeach

                        </select>
                     </div>
                     <div class="col-sm-2">
                        <select name="country" id="address_country" class="form-control select2">
                            
                            <option value="">Choose country </option>

                            @foreach($country as $val) 
                            <option value="{{$val->name}}" {{(isset($postData['country']) && $postData['country'] == $val->name)?'selected':''}}>{{$val->name}} </option>
                            @endforeach

                        </select>
                     </div>
                     <div class="col-sm-2">
                        <select name="state" id="address_state" class="form-control select2">
                            
                            <option value="">Choose state </option>

                            @foreach($state as $val2) 
                                <option value="{{$val2->name}}" {{(isset($postData['state']) && $postData['state'] == $val2->name)?'selected':''}}>{{$val2->name}} </option>
                            @endforeach

                        </select>
                     </div>
                     <div class="col-sm-2">
                        <select name="city" id="address_city" class="form-control select2">                        
                            <option value="">Choose city </option>
                            @foreach($city as $val3) 
                            <option value="{{$val3->name}}" {{(isset($postData['city']) && $postData['city'] == $val3->name)?'selected':''}}>{{$val3->name}} </option>
                            @endforeach
                        </select>
                     </div>
                     </div>
                      <div class="row">
                     <div class="pull-right" style="margin: 13px">
                        <a href="{{ url('/admin/branding/') }}" class="btn bg-purple save-btn">Clear</a>
                            <button type="submit" class="btn bg-purple save-btn">Search</button>
                        </div>
                   </div>
                </div>
            </form>


            
            <form class="form-horizontal" action="{{ url('/admin/brandingsave/') }}" method="post" enctype="multipart/form-data"> 
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                 
                @if($business)
                <div class="content">

                    <div class="row"> 
                        <div class="box-body">
                         <div class="form-group">
                            <div class="col-sm-12">
                             <h2> Create Group For Branding </h2>
                            </div>
                        </div>
                        </div>
                        </div>
                    <div class="row">
                     <div class="col-sm-4">

                        <input type="text" name="group_title" class="form-control" value="" placeholder="Branding group name" />
                     </div> 
                     <div class="col-sm-4">
                     <select name="business_id[]" multiple="multiple" class="form-control select2">
                        @foreach($business as $busi)
                     
                        <option value="{{$busi->id}}">{{$busi->name}} </option>  
                        
                        @endforeach

                    </select>
                    </div>
                    <div class="col-sm-2"> <div class="pull-right" style="margin: 13px">
                            
                                <button type="submit" class="btn bg-purple save-btn">Create Group</button>
                            </div>
                       </div>
                   </div>
                 </div>
               </div>
                @endif
            
            </form>
            @endcan
          </div>
            <!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
    
    
</section><!-- /.content -->
@stop
  
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script>


    $(function() {
        $("[data-toggle=popover]").popover();
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

    $('#filetype').change(function(){

        if($('#filetype').val() == 1)
        {
            $('#image-part').show();
            $('#video-part').hide();
            $('#text-part').hide();
        }
        else if($('#filetype').val() == 2)
        {
            $('#video-part').show();
            $('#image-part').hide();
            $('#text-part').hide();
        }
        else if($('#filetype').val() == 3)
        {
            $('#text-part').show();
            $('#video-part').hide();
            $('#image-part').hide();
        }
    });
    $("#addbrandingManagement").validate({
        ignore: "",
        rules: {
            type: {
                required: true,
            }
        },
    }); 
    $('#business_id').select2({
        placeholder: 'Select Business',
        ajax: {
            url: '{{ url("admin/auto-complete-business") }}',
            dataType: 'json',
            delay: 250,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
</script>
@stop

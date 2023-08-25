@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />

@stop
@section('content')
<section class="content-header">
    <h1>
    {{trans('labels.site')}}
    </h1>
</section>
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        @empty($site)
                        {{trans('labels.addbtn')}}
                        @else
                        {{trans('labels.editlbl')}}
                        @endempty
                        {{trans('labels.site')}}
                    </h3>
                </div><!-- /.box-header -->
                @if(Session::has('success'))
                <div class="alert alert-success">
                    {{ Session::get('success') }}
                    @php
                    Session::forget('success');
                    @endphp
                </div>
                @endif
                <form id="site-form" class="form-horizontal" method="post" action="{{route('site.store')}}" enctype="multipart/form-data" novalidate="novalidate">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{$site->id ?? ''}}" />
                    <div class="box-body">
                        <div class="form-group" >
                            <?php
                            if (old('name'))
                                $name = old('name');
                            elseif (!empty($site))
                                $name = $site->name;
                            else
                                $name = '';
                            ?>
                            <label for="name" class="col-sm-2 control-label">{{trans('labels.name')}}<span class="star_red">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="name" value="{{$name}}" class="form-control" placeholder="Please enter name">
                                @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group" >                            
                            <label for="created_by" class="col-sm-2 control-label">Site admin<span class="star_red">*</span></label>
                            <div class="col-sm-6">
                                <select id="created_by" name="created_by" class="form-control" style="cursor: pointer;" >
                                    <option value="">Select Site admin</option>
                                    @if(!empty($site) && !empty($site->createdBy))    
                                    <option value="{{$site->createdBy->id}}" selected>{{$site->createdBy->name}}</option>
                                    @endif  
                                </select>
                                @if ($errors->has('created_by'))
                                <span class="text-danger">{{ $errors->first('created_by') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group" >
                            <?php
                            if (old('visibility'))
                                $visibility = old('visibility');
                            elseif (!empty($site))
                                $visibility = $site->getRawOriginal('visibility');
                            else
                                $visibility = 0;
                            ?>
                            <label for="visibility" class="col-sm-2 control-label">Visibility<span class="star_red">*</span></label>
                            <div class="col-sm-6">
                                <select id="visibility" name="visibility" class="form-control" style="cursor: pointer;" >
                                    <option value="false" @if($visibility == 0) selected @endif>Public</option>    
                                    <option value="true" @if($visibility == 1) selected @endif>Private</option>    
                                </select>
                                @if ($errors->has('visibility'))
                                <span class="text-danger">{{ $errors->first('visibility') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group" id="join-req-section">
                            <label  class="col-sm-2 control-label">
                                Join Request
                            </label>
                            <div class="col-sm-6">
                                <label class="switch">
                                    <input type="checkbox" id="is_enable_request" name="is_enable_request"  autocomplete="off" value="true" @if(!empty($site) && $site->is_enable_request) checked @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group" >
                            <?php
                            if (old('asset_type_id'))
                                $asset_type_id = old('asset_type_id');
                            elseif (!empty($site))
                                $asset_type_id = $site->asset_type_id;
                            else
                                $asset_type_id = '';
                            ?>
                            <label for="asset_type_id" class="col-sm-2 control-label">{{trans('labels.category')}}<span class="star_red">*</span></label>
                            <div class="col-sm-6">
                                <select id="asset_type_id" name="asset_type_id" class="form-control" style="cursor: pointer;" >
                                    <option value="">Select {{trans('labels.category')}}</option>    
                                </select>
                                @if ($errors->has('asset_type_id'))
                                <span class="text-danger">{{ $errors->first('asset_type_id') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="col-sm-2 control-label">{{trans('labels.description')}}</label>
                            <div class="col-sm-6">
                                <textarea name="description" class="form-control">{{ old('description',$site->description ?? '') }}</textarea>
                                @if ($errors->has('description'))
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">                            
                            <label for="logo" class="col-sm-2 control-label">{{trans('labels.logo')}}</label>
                            <div class="col-sm-4">
                                <input type="file" id="logo" name="logo">
                            </div>                            
                        </div>
                        @if(!empty($site))
                            <div class="form-group">
                                <label class="col-sm-2 control-label">&nbsp;</label>
                                <div class="col-sm-8">
                                    <a href="{{$site->logo}}" target="_blank">
                                        <img src="{{$site->logo}}" width="50" height="50"/>
                                    </a>
                                </div>
                            </div>
                        @endif
                        <div class="form-group">                            
                            <label for="cover_photo" class="col-sm-2 control-label">Cover photo</label>
                            <div class="col-sm-4">
                                <input type="file" id="cover_photo" name="images[]">
                            </div>                            
                        </div>
                        @if(!empty($site) && $site->images->isNotEmpty())
                            <div class="form-group">
                                <label class="col-sm-2 control-label">&nbsp;</label>
                                <div class="col-sm-8">
                                    @foreach($site->images as $image)
                                    <a href="{{$image->url}}" target="_blank">
                                        <img src="{{$image->url}}" width="50" height="50"/>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="form-group" >
                            <?php
                            if (old('link'))
                                $link = old('link');
                            elseif (!empty($site))
                                $link = $site->link;
                            else
                                $link = '';
                            ?>
                            <label for="link" class="col-sm-2 control-label">Website</label>
                            <div class="col-sm-6">
                                <input type="text" name="link" value="{{$link}}" class="form-control" placeholder="Please enter website url">
                                @if ($errors->has('link'))
                                <span class="text-danger">{{ $errors->first('link') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group" >
                            <?php
                                $socials = (!empty($site)) ? $site->socials->pluck('url','name')->toArray() : [];
                            ?>
                            <label class="col-sm-2 control-label">Facebook</label>
                            <div class="col-sm-6">
                                <input type="text" name="socials[facebook]" value="{{ $socials['facebook'] ?? ''}}" class="form-control" placeholder="Please enter facebook url">
                            </div>
                        </div>
                        <div class="form-group" >
                            <label for="link" class="col-sm-2 control-label">Twitter</label>
                            <div class="col-sm-6">
                                <input type="text" name="socials[twitter]" value="{{ $socials['twitter'] ?? ''}}" class="form-control" placeholder="Please enter twitter url">
                            </div>
                        </div>
                        <div class="form-group" >
                            <label for="link" class="col-sm-2 control-label">LinkedIn</label>
                            <div class="col-sm-6">
                                <input type="text" name="socials[linkedin]" value="{{ $socials['linkedin'] ?? ''}}" class="form-control" placeholder="Please enter linkedin url">
                            </div>
                        </div>
                        <div class="form-group" >                            
                            <label for="link" class="col-sm-2 control-label">Instagram</label>
                            <div class="col-sm-6">
                                <input type="text" name="socials[instagram]" value="{{ $socials['instagram'] ?? ''}}" class="form-control" placeholder="Please enter instagram url">
                            </div>
                        </div>
                        
                        <div class="form-group" >                     
                            <label  class="col-sm-2 control-label">Contact Name</label>
                            <div class="col-sm-6">
                                <input type="text" name="contact_name" value="{{ $contacts->name ?? ''}}" class="form-control" placeholder="Please enter contact name">
                            </div>
                        </div>
                        
                        <div class="form-group" >                            
                            <label for="link" class="col-sm-2 control-label">Mobile Number</label>
                            <div class="col-sm-6">
                                <input type="text" name="mobile_no" value="{{ $contacts->mobile_no ?? ''}}" class="form-control" placeholder="Please enter contact mobile number">
                            </div>
                        </div>

                        <div class="form-group" >                            
                            <label  class="col-sm-2 control-label">Street Address</label>
                            <div class="col-sm-6">
                                <input type="text" name="address" value="{{ $contacts->address ?? ''}}" class="form-control" placeholder="Please enter address">
                            </div>
                        </div>
                        
                        <div class="form-group" >                            
                            <label class="col-sm-2 control-label">Pincode</label>
                            <div class="col-sm-6">
                                <input type="text" id="pincode" name="pincode" value="{{ $contacts->pincode ?? ''}}" class="form-control" placeholder="Please enter pincode">
                            </div>
                        </div>
                        <div class="form-group" >                            
                            <label class="col-sm-2 control-label">Country</label>
                            <div class="col-sm-6">
                                <input type="text" id="country"  class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group" >                            
                            <label class="col-sm-2 control-label">State</label>
                            <div class="col-sm-6">
                                <input type="text" id="state"  class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group" >                            
                            <label class="col-sm-2 control-label">District</label>
                            <div class="col-sm-6">
                                <input type="text" id="district" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group" >                            
                            <label class="col-sm-2 control-label">City</label>
                            <div class="col-sm-6">
                                <select id="location" name="location_id" class="form-control" style="cursor: pointer;" >
                                    <option value="">Select {{trans('labels.city')}}</option>    
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group" >
                            <?php
                            if (old('status'))
                                $status = old('status');
                            elseif (!empty($site))
                                $status = $site->getRawOriginal('status');
                            else
                                $status = 1;
                            ?>
                            <label for="status" class="col-sm-2 control-label">Status</label>
                            <div class="col-sm-6">
                                <select name="status" class="form-control" style="cursor: pointer;" >
                                    <option value="1" @if($status == 1) selected @endif>Active</option>    
                                    <option value="0" @if($status == 0) selected @endif>Inactive</option>    
                                </select>
                                @if ($errors->has('visibility'))
                                <span class="text-danger">{{ $errors->first('visibility') }}</span>
                                @endif
                            </div>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">Save</button>
                            <a class="btn btn-default" href="{{route('site.index')}}">Cancel</a>
                        </div>
                    </div><!-- /.box-footer -->
                </form>
            </div>
        </div>
    </div>
</section>
@stop
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        
        getSubAsset("{{$asset_type_id}}");

        $('#created_by').select2({
            placeholder: 'Select Site Admin',
            ajax: {
                url: '{{ route("autoCompleteUser") }}',
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

        $.validator.addMethod('filesize', function (value, element, param) {
            return this.optional(element) || (element.files[0].size <= param)
        }, 'File size must be less than 5 MB');

        $("#site-form").validate({
            rules: {
                name:{
                    required: true
                },
                created_by:{
                    required: true,
                },
                visibility:{
                    required: true,
                },
                asset_type_id:{
                    required: true,
                },
                mobile_no:{
                    number: true,
                },
                pincode:{
                    number: true,
                },
                logo:{
                    extension: "jpeg|jpg|bmp|png",
                    filesize: 5000000 // 5 mb
                },
                "images[]":{
                    extension: "jpeg|jpg|bmp|png",
                    filesize: 5000000 // 5 mb
                },
                link:{
                    url:true
                },
                "socials[facebook]":{
                    url:true
                },
                "socials[twitter]":{
                    url:true
                },
                "socials[linkedin]":{
                    url:true
                },
                "socials[instagram]":{
                    url:true
                }
            }
        });

        $("#visibility").change(function() {
            
            var visibility = $(this).val();
            if(visibility == 'false') {
                $("#is_enable_request").prop('checked',true);
                $("#join-req-section").hide();
            } else {
                $("#join-req-section").show();
            }
        });
        $("#pincode").change(function() {
            
            getCityByPincode($(this).val());
        });

        // initilise
        $("#visibility").trigger('change');
        let pincode = $("#pincode").val();
        let locationId = "{{$contacts->location_id ?? ''}}";
        getCityByPincode(pincode,locationId);
    });
    function getSubAsset(selected = '') {
        $('#asset_type_id')
                .find('option')
                .remove()
                .end()
                .append('<option value="" selected>Select {{trans("labels.category")}}</option>');
        $.ajax({
            type: "POST",
            url: "{{route('getSubAssetByAsset')}}",
            data: {assetName : "Site"},
            success: function( response ) {
                $.each(response.data, function(key, value) {   
                    $('#asset_type_id').append($("<option></option>")
                                        .attr("value",value.id)
                                        .text(value.name)); 
                });

                if(selected != '') {
                    $("#asset_type_id").val(selected).trigger('change.select2');
                }
            }
        });
    }

    function getCityByPincode(pincode,selected = '') {
        $("#country").val("");
        $("#state").val("");
        $("#district").val("");
        $('#location')
                .find('option')
                .remove()
                .end()
                .append('<option value="" selected>Select {{trans("labels.city")}}</option>');
        if(pincode != '' && pincode > 0) {
            $.ajax({
                type: "POST",
                url: "{{route('getLocationByPincode')}}",
                data: {pincode : pincode},
                success: function( response ) {
                    $.each(response.data, function(key, value) {   
                        $('#location').append($("<option></option>")
                                            .attr("value",value.location_id)
                                            .text(value.city)); 
                    });
    
                    if(selected != '') {
                        $("#location").val(selected).trigger('change.select2');
                    }
                    if(response.status) {
                        let location = response.data[0];
                        $("#country").val(location.country_name);
                        $("#state").val(location.state);
                        $("#district").val(location.district);
                    } else {
                        alert("Please enter a valid pincode..");
                        $("#pincode").val("");
                    }
                }
            });
        }
    }
</script>
@stop
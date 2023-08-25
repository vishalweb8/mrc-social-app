@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />

@stop
@section('content')
<section class="content-header">
    <h1>
    {{trans('labels.reason')}}
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
                        @empty($reason)
                        {{trans('labels.addbtn')}}
                        @else
                        {{trans('labels.editlbl')}}
                        @endempty
                        {{trans('labels.reason')}}
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
                <form class="form-horizontal" method="post" action="{{route('reason.store')}}" enctype="multipart/form-data" novalidate="novalidate">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{$reason->id ?? ''}}" />
                    <div class="box-body">
                        <div class="form-group">
                            <?php
                            if (old('asset_type'))
                                $asset_type = old('asset_type');
                            elseif (!empty($reason))
                                $asset_type = !empty($reason->assetType->parent) ? $reason->assetType->parent : $reason->asset_type_id ;
                            else
                                $asset_type = '';
                            ?>
                            <label for="type" class="col-sm-2 control-label">{{trans('labels.asset_type')}}<span class="star_red">*</span></label>
                            <div class="col-sm-6">
                                <select id="type" name="asset_type" class="form-control" style="cursor: pointer;" >
                                    <option value="" selected>Select {{trans('labels.asset_type')}}</option>
                                    @forelse(assetTypes() as $assetType)
                                        <option value="{{$assetType->id}}" @if($asset_type == $assetType->id) selected @endif>
                                            {{$assetType->name}}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                                @if ($errors->has('asset_type'))
                                <span class="text-danger">{{ $errors->first('asset_type') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group" >
                            <?php
                            if (old('sub_asset_type'))
                                $sub_asset_type = old('sub_asset_type');
                            elseif (!empty($reason))
                                $sub_asset_type = $reason->asset_type_id;
                            else
                                $sub_asset_type = '';
                            ?>
                            <label for="sub_asset_type" class="col-sm-2 control-label">{{trans('labels.sub_asset_type')}}<!-- <span class="star_red">*</span> --></label>
                            <div class="col-sm-6">
                                <select id="sub_asset_type" name="sub_asset_type" class="form-control" style="cursor: pointer;" >
                                    <option value="">Select {{trans('labels.sub_asset_type')}}</option>    
                                </select>
                                @if ($errors->has('sub_asset_type'))
                                <span class="text-danger">{{ $errors->first('sub_asset_type') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="reason" class="col-sm-2 control-label">{{trans('labels.reason')}}<span class="star_red">*</span></label>
                            <div class="col-sm-6">
                                <textarea name="reason" class="form-control">{{ old('reason',$reason->reason ?? '') }}</textarea>
                                @if ($errors->has('reason'))
                                <span class="text-danger">{{ $errors->first('reason') }}</span>
                                @endif
                            </div>
                        </div>                        
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">Save</button>
                            <a class="btn btn-default" href="{{route('reason.index')}}">Cancel</a>
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
        $("#type").on('change',function(e) {
            getSubAsset($(this).val());
        });
        getSubAsset( $("#type").val(), "{{$sub_asset_type}}");
    });
    function getSubAsset(assetId, selected = '') {
        $('#sub_asset_type')
                        .find('option')
                        .remove()
                        .end()
                        .append('<option value="" selected>Select {{trans("labels.sub_asset_type")}}</option>');
        if(assetId > 0) {
            $.ajax({
                type: "POST",
                url: "{{route('getSubAssetByAsset')}}",
                data: {assetId : assetId},
                success: function( response ) {
                    $.each(response.data, function(key, value) {   
                        $('#sub_asset_type').append($("<option></option>")
                                            .attr("value",value.id)
                                            .text(value.name)); 
                    });

                    if(selected != '') {
                        $("#sub_asset_type").val(selected).trigger('change.select2');
                    }
                }
            });
        }
    }
</script>
@stop
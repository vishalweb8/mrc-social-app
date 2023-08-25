@extends('Admin.Master')
@section('header')
<link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.tag-editor.css') }}" />
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />

@stop
@section('content')
@php
$parent = (!empty($assetType)) ? $assetType->parent : $parent;
$isEntity = isEntity($parent);
@endphp
<section class="content-header">
    <h1>
        @if($parent > 0)
        {{trans('labels.sub_asset_type')}}
        @else
        {{trans('labels.asset_type')}}
        @endif
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
                        @empty($assetType)
                        {{trans('labels.addbtn')}}
                        @else
                        {{trans('labels.editlbl')}}
                        @endempty
                        @if($parent > 0)
                        {{trans('labels.sub_asset_type')}}
                        @else
                        {{trans('labels.asset_type')}}
                        @endif
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
                <form class="form-horizontal" method="post" action="{{route('assetType.store')}}" enctype="multipart/form-data" novalidate="novalidate">
                    @csrf
                    <input type="hidden" name="id" id="asset_type" value="{{$assetType->id ?? ''}}" />
                    <input type="hidden" name="parent" value="{{ $parent }}" />
                    <div class="box-body">
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Name<span class="star_red">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="name" placeholder="Name" value="{{ old('name',$assetType->name ?? '') }}">
                                @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                        </div>
                        @if($parent > 0 && $isEntity)
                        <div class="form-group">
                            <label for="category" class="col-sm-2 control-label">Category</label>
                            <div class="col-sm-6">
                                <select name="category_id" id="category_id" class="form-control  select2">
                                    <option value="">All Category </option>
                                    @foreach(getCategories()  as $category) 
                                        <option value="{{$category->id}}" >{{$category->name}} </option>
                                    @endforeach
                                </select>
                                
                            </div>
                        </div>
                        @php
                            $components = [];
                            $fields = null;
                            if(!empty($assetType)) {
                                $fields = $assetType->fields()->where('category_id',0)->first();
                            }
                            if($fields && !empty($fields->selected_fields)) {
                                $components = json_decode($fields->selected_fields,true);
                            }
                        @endphp
                        <div id="component-section">
                            @include('Admin.AssetType.SubAssetType.entity_fields',['components'=>$components])
                        </div>
                        @endif
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">Save</button>
                            @if($parent > 0)
                            <a class="btn btn-default" href="{{route('getSubAssetTypeByAsset',$parent)}}">Cancel</a>
                            @else
                            <a class="btn btn-default" href="{{route('assetType.index')}}">Cancel</a>
                            @endif
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
<script>
    $(document).ready(function() {
        $('#category_id').select2();
        $("#category_id").on('change',function() {
            getSelectedFields($(this).val());
        });

        function getSelectedFields(categoryId) {
            $.ajax({
                type: "POST",
                url: "{{route('getSubAssetTypeFields')}}",
                data: {category_id: categoryId, asset_type_id: $("#asset_type").val(), from: 'admin'},
                success: function( response ) { 
                    $("#component-section").html(response.data);
                }
            });
        }
    });
</script>
@stop
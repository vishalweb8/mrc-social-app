@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	Public Website Tetemplets
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">Edit Public Website Tetemplets</h3>
					</div><!-- /.box-header -->
					<form id="addinvestment" class="form-horizontal" method="post" action="{{route('PublicWebsiteTetemplets.update',$PublicWebsiteTemplets->id)}}" enctype="multipart/form-data" novalidate="novalidate">
						@csrf
						{{-- <input type="hidden" name="_token" value="fEiYsKGPUou1s3GIK3x4fke0O9NCUm0yBx0546uU">
						<input type="hidden" name="id" value="0">
						<input type="hidden" name="user_id" value="1"> --}}
						
						
						<div class="box-body">
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Template Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_name" name="template_name" value="{{$PublicWebsiteTemplets->template_name}}">
									 @if ($errors->has('template_name'))
					                    <span class="text-danger">{{ $errors->first('template_name') }}</span>
					                @endif
								</div>
							</div>
							<div class="form-group" id="preview_image">

										<?php
		                            if (old('preview_image'))
		                                $photo = old('preview_image');
		                            elseif (isset($PublicWebsiteTemplets))
		                                $photo = $PublicWebsiteTemplets->preview_image;
		                            else
		                                $photo = '';
		                            ?>
		                            <label for="title" class="col-sm-2 control-label">Preview Image<span class="star_red">*</span></label>
									<div class="col-sm-8">
										<input type="file" class="form-control" id="owner_images" name="preview_image" >
										 @if ($errors->has('preview_image'))
						                    <span class="text-danger">{{ $errors->first('preview_image') }}</span>
						                @endif
									</div>                          
		                        </div>

		                        @if(isset($PublicWebsiteTemplets) && !empty($PublicWebsiteTemplets))
		                            @if($PublicWebsiteTemplets->preview_image != '')
		                                <div class="form-group" id="owner_images">
		                                    <label for="media_images" class="col-sm-2 control-label">&nbsp;</label>
		                                    <div class="col-sm-8">
		                                        @if(Storage::disk(config('constant.DISK'))->exists(Config::get('constant.PUBLIC_WEBSITE_THUMBNAIL_IMAGE').$PublicWebsiteTemplets->preview_image)) 
		                                            <img src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.PUBLIC_WEBSITE_THUMBNAIL_IMAGE').$PublicWebsiteTemplets->preview_image) }}" width="50" height="50"/>
		                                        @endif                                                
		                                    </div>
		                                </div>
		                            @endif
		                        @endif

								
							<div class="form-group">
								<?php
		                            if (old('preview_image'))
		                                $photo = old('preview_image');
		                            elseif (isset($PublicWebsiteTemplets))
		                                $photo = $PublicWebsiteTemplets->preview_image;
		                            else
		                                $photo = '';
		                            ?>
								<label for="title" class="col-sm-2 control-label">Template Html<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<textarea type="logtext" class="form-control" id="website_name" name="template_html" >{{$PublicWebsiteTemplets->template_html}}</textarea>
									 @if ($errors->has('template_html'))
				                    <span class="text-danger">{{ $errors->first('template_html') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="website_theme" class="col-sm-2 control-label">Template Theme<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_theme" name="template_theme" placeholder="Template Theme" value="{{ old('template_theme',$PublicWebsiteTemplets->template_theme) }}" >
									@if ($errors->has('template_theme'))
				                    <span class="text-danger">{{ $errors->first('template_theme') }}</span>
				                @endif
								</div>
							</div>
							
							</div><!-- /.box-body -->
							<div class="box-footer">
								<div class="pull-right">
									<button type="submit" class="btn bg-purple save-btn">Save</button>
									<a class="btn btn-default" href="{{route('PublicWebsiteTetemplets.list')}}">Cancel</a>
								</div>
								</div><!-- /.box-footer -->
							</form>
						</div>
					</div>
				</div>
							</div>

			</section>
			@stop
			@section('script')
			<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>

			@stop
@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	Public Website Templates
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">Add Public Website Template</h3>
					</div><!-- /.box-header -->
					 @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
					<form id="addinvestment" class="form-horizontal" method="post" action="{{route('PublicWebsiteTetemplets.store')}}" enctype="multipart/form-data" novalidate="novalidate">
						@csrf
						<div class="box-body">
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Template Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_name" name="template_name" placeholder="Template Name" value="{{ old('template_name') }}" >
									@if ($errors->has('template_name'))
				                    <span class="text-danger">{{ $errors->first('template_name') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Preview Image<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="file" class="form-control" id="website_name" name="preview_image" placeholder="Website Name" >
									@if ($errors->has('preview_image'))
				                    <span class="text-danger">{{ $errors->first('preview_image') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Template Html<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<textarea type="file" class="form-control" id="template_html" name="template_html" placeholder="Template Html" >
										{{ old('template_html') }}
									</textarea>
									@if ($errors->has('template_html'))
				                    <span class="text-danger">{{ $errors->first('template_html') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="website_theme" class="col-sm-2 control-label">Template Theme<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_theme" name="template_theme" placeholder="Template Theme" value="{{ old('template_theme') }}" >
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
			</section>
			@stop
			@section('script')
			<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
			{{-- <script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
			<script type="text/javascript">
				 CKEDITOR.replace('template_html');
			</script> --}}
			@stop
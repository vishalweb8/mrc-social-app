@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	Public Website
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">Add Public Website</h3>
					</div><!-- /.box-header -->
					 @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
					<form  class="form-horizontal" method="post" action="{{route('publicwebsite.store',$business->id)}}" enctype="multipart/form-data" novalidate="novalidate">
						
						@csrf
					<div class="box-body">
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Business Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_name" name="business_name" value="{{$business->name}}" placeholder="fd"  disabled>
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Template Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<select name="template_name" class="form-control" id="template_name">
                                    <option value="">Select type</option>
									@foreach($PublicWebsiTetemplets as $PublicWebsiTetempletsGet)
										<option value="{{$PublicWebsiTetempletsGet->id}}" {{(old('template_name')==$PublicWebsiTetempletsGet->id)? 'selected':''}}>{{$PublicWebsiTetempletsGet->template_name}}</option>	
                                   	@endforeach 
                                </select>
                                 @if ($errors->has('template_name'))
				                    <span class="text-danger">{{ $errors->first('template_name') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Template Theme<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<select name="template_theme" class="form-control" id="template_theme">
									<option value="">Select</option>
                                	</select>
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Website Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_name" name="website_name" placeholder="Website Name" value="{{ old('website_name') }}">
								 @if ($errors->has('website_name'))
				                    <span class="text-danger">{{ $errors->first('website_name') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Plan Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<select name="plan_name" class="form-control" id="filetype">
                                    <option value="">Select type</option>
									@foreach($PublicWebsitePlans as $PublicWebsitePlansGet)
										<option value="{{$PublicWebsitePlansGet->id}}" {{(old('plan_name')==$PublicWebsitePlansGet->id)? 'selected':''}}>{{$PublicWebsitePlansGet->pw_plan_name}}</option>	
                                   	@endforeach 
                                </select>
                                 @if ($errors->has('plan_name'))
				                    <span class="text-danger">{{ $errors->first('plan_name') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Public Website Type<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<select name="pw_type" class="form-control type">
	                                    <option value="1">RYUVA</option>
	                                    <option value="2">Domain</option>
	                                </select>
								</div>
							</div>
							<div class="form-group" id="domain">
								{{-- <label for="title" class="col-sm-2 control-label">Domain Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_name" name="website_name" placeholder="Website Name" >
								</div> --}}
							</div>
							</div><!-- /.box-body -->
							<div class="box-footer">
								<div class="pull-right">
									<button type="submit" class="btn bg-purple save-btn">Save</button>
									<a class="btn btn-default" href="{{route('publicwebsite.list')}}">Cancel</a>
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
 $('.type').change(function(){
 	var value = $(this).val();
	 	if(value == 2){
		 	$('#domain').html('<label for="title" class="col-sm-2 control-label">Domain Name<span class="star_red">*</span></label><div class="col-sm-8"><input type="text" class="form-control" id="website_name" name="pw_domain" placeholder="Domain Name" value="{{ old('pw_domain') }}" >@if ($errors->has('pw_domain'))<span class="text-danger">{{ $errors->first('pw_domain') }}</span>@endif</div>');

		 }else{
		 	$('#domain').html('');
		 }
      
    });

	$(document).ready(function() {
		$('#template_name').change(function(){
			getThemeOption($(this).val());
		});
	});

	function getThemeOption(templateId,defaultValue = '') {
		$('#template_theme').html('');
		$.ajax({
			url: "{{route('PublicWebsiteTetemplets.getTheme')}}",
			data: {
				template_id: templateId
			},
			success: function(response) {
				if (response.status) {
					$('#template_theme').html(response.data);
					if(defaultValue != '') {
						$('#template_theme').val(defaultValue);
					}
				}
			},
			error: function(xhr) {
				console.log(xhr);
			}      
   	 	});
	}
 
</script>
@stop
@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	{{trans('labels.business_branding')}}
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{trans('labels.addbtn')}} {{trans('labels.business_branding')}}</h3>
					</div><!-- /.box-header -->
					 @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
					<form  class="form-horizontal" method="post" action="{{route('businessBranding.store')}}" enctype="multipart/form-data" novalidate="novalidate">
						
						@csrf
						<div class="box-body">
							<div class="form-group">
								<label for="business_id" class="col-sm-2 control-label">
									{{ trans('labels.business') }}
									<span class="star_red">*</span>
								</label>
								<div class="col-sm-6">
									<select class="form-control" id="business_id" name="business_id">
										<option value="">Select Business</option>
									</select>
									@if ($errors->has('business_id'))
				                    	<span class="text-danger">{{ $errors->first('business_id') }}</span>
				                	@endif
								</div>
							</div>
							
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Start Date<span class="star_red">*</span></label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="{{ old('start_date') }}">
								 	@if ($errors->has('start_date'))
				                    	<span class="text-danger">{{ $errors->first('start_date') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">End Date<span class="star_red">*</span></label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date" value="{{ old('end_date') }}">
								 	@if ($errors->has('end_date'))
				                    	<span class="text-danger">{{ $errors->first('end_date') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Views</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="views" name="views" placeholder="Views" value="{{ old('views') }}">
								 	@if ($errors->has('views'))
				                    	<span class="text-danger">{{ $errors->first('views') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Clicks</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="clicks" name="clicks" placeholder="Clicks" value="{{ old('clicks') }}">
								 	@if ($errors->has('clicks'))
				                    	<span class="text-danger">{{ $errors->first('clicks') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Image</label>
								<div class="col-sm-6">
									<input type="file" class="form-control" id="image" name="image" >
								</div>
							</div>
						</div><!-- /.box-body -->
						<div class="box-footer">
							<div class="pull-right">
								<button type="submit" class="btn bg-purple save-btn">Save</button>
								<a class="btn btn-default" href="{{route('businessBranding.index')}}">Cancel</a>
							</div>
						</div><!-- /.box-footer -->
					</form>
				</div>
			</div>
		</div>
	</section>
@stop
@section('script')
@include('Admin.Common.script')
<script>
	$(document).ready(function() {
		$('#start_date,#end_date').datepicker({
			autoclose: true,
			format: 'yyyy-mm-dd'
		});
		

		$('#start_date').change(function(){
			let startDate = $(this).val();		
			//$('#end_date').datepicker("change",{ startDate: new Date(startDate)});			
		});
	});
</script>
@stop
@extends('Admin.Master')
@section('header')
<link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.tag-editor.css') }}" />
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	{{trans('labels.plasma_donor')}}
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{trans('labels.addbtn')}} {{trans('labels.plasma_donor')}}</h3>
					</div><!-- /.box-header -->
					 @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
					<form  class="form-horizontal" method="post" action="{{route('plasmaDonor.store')}}" enctype="multipart/form-data" novalidate="novalidate">
						
						@csrf
						<div class="box-body">							
							<div class="form-group">
								<label for="name" class="col-sm-2 control-label">Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  name="name" placeholder="Name" value="{{ old('name') }}">
								 	@if ($errors->has('name'))
				                    	<span class="text-danger">{{ $errors->first('name') }}</span>
				                	@endif
								</div>
							</div>
                            <div class="form-group">
								<label for="mobile_number" class="col-sm-2 control-label">Mobile Number<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  name="mobile_number" placeholder="Mobile Number" value="{{ old('mobile_number') }}">
								 	@if ($errors->has('mobile_number'))
				                    	<span class="text-danger">{{ $errors->first('mobile_number') }}</span>
				                	@endif
								</div>
							</div>

                            <div class="form-group">
								<label for="blood_group" class="col-sm-2 control-label">Blood Group</label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  name="blood_group" placeholder="Blood Group" value="{{ old('blood_group') }}">
								 	@if ($errors->has('blood_group'))
				                    	<span class="text-danger">{{ $errors->first('blood_group') }}</span>
				                	@endif
								</div>
							</div>
                            <div class="form-group">
								<label for="city" class="col-sm-2 control-label">City</label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  name="city" placeholder="City" value="{{ old('city') }}">
								 	@if ($errors->has('city'))
				                    	<span class="text-danger">{{ $errors->first('city') }}</span>
				                	@endif
								</div>
							</div>
                            <div class="form-group">
								<label for="covid_start_date" class="col-sm-2 control-label">Covid Start Date</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="covid_start_date"  name="covid_start_date" placeholder="Covid Start Date" value="{{ old('covid_start_date') }}">
								 	@if ($errors->has('covid_start_date'))
				                    	<span class="text-danger">{{ $errors->first('covid_start_date') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="source" class="col-sm-2 control-label">
									Status
								</label>
								<div class="col-sm-8">
									<select class="form-control"  name="status">
										<option value="active" selected>Active</option>
										<option value="inactive">Inactive</option>
									</select>
								</div>
							</div>
						</div><!-- /.box-body -->
						<div class="box-footer">
							<div class="pull-right">
								<button type="submit" class="btn bg-purple save-btn">Save</button>
								<a class="btn btn-default" href="{{route('plasmaDonor.index')}}">Cancel</a>
							</div>
						</div><!-- /.box-footer -->
					</form>
				</div>
			</div>
		</div>
	</section>
@stop
@section('script')
<script>
	$(document).ready(function() {		
		$('#covid_start_date').datepicker({
			autoclose: true,
			format: 'yyyy-mm-dd'
		});
    });
</script>
@stop

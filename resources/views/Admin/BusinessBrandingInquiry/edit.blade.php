@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	{{trans('labels.business_branding_inquiry')}}
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{trans('labels.editlbl')}} {{trans('labels.business_branding_inquiry')}}</h3>
					</div><!-- /.box-header -->
					 @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
					<form  class="form-horizontal" method="post" action="{{route('businessBrandingInquiry.update',$businessBrandingInquiry->id)}}" enctype="multipart/form-data" novalidate="novalidate">
						
						@csrf
						@method('PUT')
						<div class="box-body">
							<div class="form-group">
								<label for="business_id" class="col-sm-2 control-label">
									{{ trans('labels.business') }}
								</label>
								<div class="col-sm-6">
									<select class="form-control" id="business_id" name="business_id">
										@empty($businessBrandingInquiry->business_id)
											<option value="">Select Business</option>
										@else
											<option value="{{$businessBrandingInquiry->business_id}}" selected="selected">{{$businessBrandingInquiry->business->name ?? ''}}</option>
										@endempty
									</select>
								</div>
							</div>
							
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Business Name<span class="star_red">*</span></label>
								<div class="col-sm-6">
									<input type="text" class="form-control"  name="business_name" placeholder="Business Name" value="{{ old('business_name',$businessBrandingInquiry->business_name) }}">
								 	@if ($errors->has('business_name'))
				                    	<span class="text-danger">{{ $errors->first('business_name') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Name<span class="star_red">*</span></label>
								<div class="col-sm-6">
									<input type="text" class="form-control"  name="name" placeholder="Name" value="{{ old('name',$businessBrandingInquiry->name) }}">
								 	@if ($errors->has('name'))
				                    	<span class="text-danger">{{ $errors->first('name') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Mobile Number<span class="star_red">*</span></label>
								<div class="col-sm-6">
									<input type="text" class="form-control" name="mobile_number" placeholder="Mobile Number" value="{{ old('mobile_number',$businessBrandingInquiry->mobile_number) }}">
								 	@if ($errors->has('mobile_number'))
				                    	<span class="text-danger">{{ $errors->first('mobile_number') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">City<span class="star_red">*</span></label>
								<div class="col-sm-6">
									<input type="text" class="form-control"  name="city" placeholder="City" value="{{ old('city',$businessBrandingInquiry->city) }}">
								 	@if ($errors->has('city'))
				                    	<span class="text-danger">{{ $errors->first('city') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Type</label>
								<div class="col-sm-6">
									<input type="text" class="form-control"  name="type" placeholder="Type" value="{{ old('type',$businessBrandingInquiry->type) }}">
								 	@if ($errors->has('type'))
				                    	<span class="text-danger">{{ $errors->first('type') }}</span>
				                	@endif
								</div>
							</div>
                            <div class="form-group">
								<label for="feedback" class="col-sm-2 control-label">Feedback</label>
								<div class="col-sm-6">
                                    <textarea name="feedback" class="form-control">{{ old('feedback',$businessBrandingInquiry->feedback) }}</textarea>
																	 	
								</div>
							</div>
							<div class="form-group">
								<label for="status" class="col-sm-2 control-label">
									Status
									<span class="star_red">*</span>
								</label>
								<div class="col-sm-6">
									<select class="form-control" id="status" name="status">
									<option value="pending" @if($businessBrandingInquiry->status == "pending") selected @endif>Pending </option>
									<option value="approved" @if($businessBrandingInquiry->status == "approved") selected @endif>Approved </option>
									<option value="rejected" @if($businessBrandingInquiry->status == "rejected") selected @endif>Rejected </option>
									</select>
								</div>
							</div>
						</div><!-- /.box-body -->
						<div class="box-footer">
							<div class="pull-right">
								<button type="submit" class="btn bg-purple save-btn">Save</button>
								<a class="btn btn-default" href="{{route('businessBrandingInquiry.index')}}">Cancel</a>
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
@stop
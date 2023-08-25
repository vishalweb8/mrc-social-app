@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	Public Website Plans
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">Add Public Website Plans</h3>
					</div><!-- /.box-header -->
			        @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
      
					<form id="addinvestment" class="form-horizontal" method="post" action="{{route('PublicWebsiteplans.store')}}" enctype="multipart/form-data" novalidate="novalidate">
						@csrf
						{{-- <input type="hidden" name="_token" value="fEiYsKGPUou1s3GIK3x4fke0O9NCUm0yBx0546uU">
						<input type="hidden" name="id" value="0">
						<input type="hidden" name="user_id" value="1"> --}}
						
						
						<div class="box-body">
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Plan Name<span class="star_red">*</span>
								</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="plan_name " name="pw_plan_name" placeholder="Plan Name" value="{{ old('pw_plan_name') }}"  >
				                @if ($errors->has('pw_plan_name'))
				                    <span class="text-danger">{{ $errors->first('pw_plan_name') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Plan Features<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<textarea type="file" class="form-control" id="website_name" name="plan_features" placeholder="Plan Features" >
										{{ old('plan_features') }}
									</textarea>
									
				                @if ($errors->has('plan_features'))
				                    <span class="text-danger">{{ $errors->first('plan_features') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Plan Amount<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_name" name="plan_amount" placeholder="Plan Amount" value="{{ old('plan_amount') }}" >
									 @if ($errors->has('plan_amount'))
				                    <span class="text-danger">{{ $errors->first('plan_amount') }}</span>
				                @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Plan Duration(No of Months)<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="plan_duration" name="plan_duration" placeholder="Plan Duration" value="{{ old('plan_duration') }}" >
									 @if ($errors->has('plan_duration'))
				                    <span class="text-danger">{{ $errors->first('plan_duration') }}</span>
				                @endif
								</div>
							</div>
							
							</div><!-- /.box-body -->
							<div class="box-footer">
								<div class="pull-right">
									<button type="submit" class="btn bg-purple save-btn">Save</button>
									<a class="btn btn-default" href="{{route('PublicWebsiteplans.list')}}">Cancel</a>
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
			<script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
			<script type="text/javascript">
				 CKEDITOR.replace('plan_features');
			</script>
			@stop
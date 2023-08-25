@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	Public Website Payments
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">Add Public Website Payments</h3>
					</div><!-- /.box-header -->
					 @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
					<form id="addinvestment" class="form-horizontal" method="post" action="{{route('PublicWebsitepayments.update',$publicWebsitePayments->id)}}" enctype="multipart/form-data" novalidate="novalidate">
						@csrf
						
						<div class="box-body">
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Website Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<select class="form-control" name="website_name">
										<option value="{{$publicWebsitePayments->pw_id}}">{{$publicWebsitePayments->publicWebsiteName->website_name}}</option>
										@foreach($publicWebsite as $publicWebsiteGet)
										<option value="{{$publicWebsiteGet->id}}"{{(old('website_name')==$publicWebsiteGet->id)? 'selected':''}}>{{$publicWebsiteGet->website_name}}</option>
										@endforeach
									</select>
									@if ($errors->has('website_name'))
					                    <span class="text-danger">{{ $errors->first('website_name') }}</span>
					                 @endif
									{{-- <input type="text" class="form-control" id="website_name" name="website_name" placeholder="Website Name" > --}}
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Amount<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_name" name="amount" value="{{$publicWebsitePayments->payment_amount}}" >
									@if ($errors->has('amount'))
					                    <span class="text-danger">{{ $errors->first('amount') }}</span>
					                 @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Date<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="date" class="form-control" id="website_name" name="date" placeholder="Date" value="{{$publicWebsitePayments->payment_date}}" >
									@if ($errors->has('date'))
					                    <span class="text-danger">{{ $errors->first('date') }}</span>
					                 @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Payment Transaction Id<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_name" name="pay_trans_id" value="{{$publicWebsitePayments->pay_trans_id}}" >
									@if ($errors->has('pay_trans_id'))
					                    <span class="text-danger">{{ $errors->first('pay_trans_id') }}</span>
					                 @endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Payment Message<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="website_name" name="payment_message" value="{{$publicWebsitePayments->payment_message}}" >
									@if ($errors->has('payment_message'))
					                    <span class="text-danger">{{ $errors->first('payment_message') }}</span>
					                 @endif
								</div>
							</div>
							</div><!-- /.box-body -->
							<div class="box-footer">
								<div class="pull-right">
									<button type="submit" class="btn bg-purple save-btn">Save</button>
									<a class="btn btn-default" href="{{route('PublicWebsitepayments.list')}}">Cancel</a>
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
			@stop
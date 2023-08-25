@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	{{trans('labels.send_mail')}}
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{trans('labels.send_mail')}}</h3>
					</div><!-- /.box-header -->
					 @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
					<form  class="form-horizontal" method="post" action="{{route('sendMail.update',$sendMail->id)}}" enctype="multipart/form-data" novalidate="novalidate">
						@method('put')
						@csrf
						<div class="box-body">
							<div class="form-group">
								<label for="type" class="col-sm-2 control-label">
									Type
									<span class="star_red">*</span>
								</label>
								<div class="col-sm-6">
									<select class="form-control" id="type" name="type">
										<option value="">Select Type</option>
										<option value="user" @if($sendMail->type == 'user') selected @endif>User</option>
										<option value="business" @if($sendMail->type == 'business') selected @endif>Business</option>
									</select>
									@if ($errors->has('type'))
				                    	<span class="text-danger">{{ $errors->first('type') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Start Id<span class="star_red">*</span></label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="start_id" name="start_id" placeholder="Start Id" value="{{ old('start_id',$sendMail->start_id) }}">
								 	@if ($errors->has('start_id'))
				                    	<span class="text-danger">{{ $errors->first('start_id') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">End Id<span class="star_red">*</span></label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="end_id" name="end_id" placeholder="End Id" value="{{ old('end_id',$sendMail->end_id) }}">
								 	@if ($errors->has('end_id'))
				                    	<span class="text-danger">{{ $errors->first('end_id') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="subject" class="col-sm-2 control-label">
									Subject
									<span class="star_red">*</span>
								</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" id="subject" name="subject" value="{{ old('subject',$sendMail->subject) }}" />
									@if ($errors->has('subject'))
				                    	<span class="text-danger">{{ $errors->first('subject') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Mail Body
								<span class="star_red">*</span>
								</label>
								<div class="col-sm-8">
									<textarea type="file" class="form-control" name="mail_body" placeholder="Mail Body" >
										{{ old('mail_body',$sendMail->mail_body) }}
									</textarea>
										
									@if ($errors->has('mail_body'))
										<span class="text-danger">{{ $errors->first('mail_body') }}</span>
									@endif
								</div>
							</div>
						</div><!-- /.box-body -->
						<div class="box-footer">
							<div class="pull-right">
								<button type="submit" class="btn bg-purple save-btn">Send</button>
								<a class="btn btn-default" href="{{route('sendMail.index')}}">Cancel</a>
							</div>
						</div><!-- /.box-footer -->
					</form>
				</div>
			</div>
		</div>
	</section>
@stop
@section('script')
<script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
<script type="text/javascript">
	CKEDITOR.replace('mail_body');
</script>
@stop
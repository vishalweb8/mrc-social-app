@extends('Admin.Master')
@section('header')
<link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.tag-editor.css') }}" />
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop
@section('content')
<section class="content-header">
	<h1>
	{{trans('labels.public_post')}}
	</h1>
</section>
<section class="content">
	<div class="row">
		<!-- right column -->
		<div class="col-md-12">
			<!-- Horizontal Form -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{trans('labels.editlbl')}} {{trans('labels.public_post')}}</h3>
					</div><!-- /.box-header -->
					 @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
					<form  class="form-horizontal" method="post" action="{{route('onlineStore.update',$onlineStore->id)}}" enctype="multipart/form-data" novalidate="novalidate">
						
						@csrf
						@method('put')
						<div class="box-body">							
							<div class="form-group">
								<label for="name" class="col-sm-2 control-label">Name<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  name="name" placeholder="Name" value="{{ old('name',$onlineStore->name) }}">
								 	@if ($errors->has('name'))
				                    	<span class="text-danger">{{ $errors->first('name') }}</span>
				                	@endif
								</div>
							</div>
                            <div class="form-group">
                                <label for="logo" class="col-sm-2 control-label">Logo</label>
                                <div class="col-sm-4">
                                    <input type="file" id="logo" accept="image/*"  name="logo"  >
                                </div>
                                <div class="col-sm-3">
                                    @if(!empty($onlineStore->logo))
                                        <img src="{{ $onlineStore->logo }}" width="50" height="50" class="report-image img-thumbnail"/>
                                    @else
                                        <img src="{{ url('images/default.png') }}" width="50" height="50"/>
                                    @endif
                                </div>
                                @if ($errors->has('logo'))
                                    <span class="text-danger">{{ $errors->first('logo') }}</span>
                                @endif                          
                            </div>
							<div class="form-group">
								<label for="source" class="col-sm-2 control-label">
									Status
								</label>
								<div class="col-sm-8">
									<select class="form-control"  name="status">
										<option value="1" @if($onlineStore->status == 1) selected @endif>Active</option>
										<option value="0" @if($onlineStore->status == 0) selected @endif>Inactive</option>
									</select>
								</div>
							</div>
						</div><!-- /.box-body -->
						<div class="box-footer">
							<div class="pull-right">
								<button type="submit" class="btn bg-purple save-btn">Save</button>
								<a class="btn btn-default" href="{{route('onlineStore.index')}}">Cancel</a>
							</div>
						</div><!-- /.box-footer -->
					</form>
				</div>
			</div>
		</div>
	</section>
@stop
@section('script')

@stop

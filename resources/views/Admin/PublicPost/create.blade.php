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
					<h3 class="box-title">{{trans('labels.addbtn')}} {{trans('labels.public_post')}}</h3>
					</div><!-- /.box-header -->
					 @if(Session::has('success'))
				        <div class="alert alert-success">
				            {{ Session::get('success') }}
				            @php
				                Session::forget('success');
				            @endphp
				        </div>
				        @endif
					<form  class="form-horizontal" method="post" action="{{route('publicPost.store')}}" enctype="multipart/form-data" novalidate="novalidate">
						
						@csrf
						<div class="box-body">							
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Title<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  name="title" placeholder="Title" value="{{ old('title') }}">
								 	@if ($errors->has('title'))
				                    	<span class="text-danger">{{ $errors->first('title') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="category" class="col-sm-2 control-label">
									Category
									<span class="star_red">*</span>
								</label>
								<div class="col-sm-8">
									<select class="form-control" id="category" name="category">
										<option value="">Select</option>
										@if($categories)
											@foreach(explode(',',$categories) as $category)
												<option value="{{$category}}">{{$category}}</option>
											@endforeach
										@endif
									</select>
									@if ($errors->has('category'))
				                    	<span class="text-danger">{{ $errors->first('category') }}</span>
				                	@endif
								</div>
							</div>
							<div class="form-group">
								<label for="title" class="col-sm-2 control-label">Content
								<span class="star_red">*</span>
								</label>
								<div class="col-sm-8">
									<textarea type="file" class="form-control" name="content" placeholder="Content" >{{ old('content') }}</textarea>
										
									@if ($errors->has('content'))
										<span class="text-danger">{{ $errors->first('content') }}</span>
									@endif
								</div>
							</div>
							<div class="form-group">
								<label for="source" class="col-sm-2 control-label">
									Source
								</label>
								<div class="col-sm-8">
									<select class="form-control" id="source" name="source">
										<option value="">Select</option>
										<option value="myself">Myself</option>
										<option value="external">External</option>
									</select>
								</div>
							</div>
							<div class="form-group external-section" style="display: none;">
								<label for="title" class="col-sm-2 control-label">External Link</label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  name="external_link" placeholder="External Link" value="{{ old('external_link') }}">
								</div>
							</div>
							<div class="form-group">
								<label for="post_keywords" class="col-sm-2 control-label">Post Keywords</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="post_keywords"  name="post_keywords"  value="{{ old('post_keywords') }}">
								</div>
							</div>
							<div class="form-group">
								<label for="moderator_keywords" class="col-sm-2 control-label">
									Moderator Keywords
								</label>
								<div class="col-sm-8">
									<select class="form-control" id="moderator_keywords" name="moderator_keywords">
										<option value="">Select</option>
										@if($keywords)
											@foreach(explode(',',$keywords) as $keyword)
												<option value="{{$keyword}}">{{$keyword}}</option>
											@endforeach
										@endif
									</select>
									@if ($errors->has('moderator_keywords'))
				                    	<span class="text-danger">{{ $errors->first('moderator_keywords') }}</span>
				                	@endif
								</div>
							</div>
                            <div class="form-group">
                                <label for="images" class="col-sm-2 control-label">{{trans('labels.images')}}</label>
                                <div class="col-sm-4">
                                    <input type="file" id="images" accept="image/*"  name="images[]" multiple >
                                </div>  
                                @if ($errors->has('images'))
                                    <span class="text-danger">{{ $errors->first('images') }}</span>
                                @endif                          
                            </div>
							<div class="form-group">
								<label for="source" class="col-sm-2 control-label">
									Status
								</label>
								<div class="col-sm-8">
									<select class="form-control"  name="status">
										<option value="">Select</option>
										<option value="draft">Draft</option>
										<option value="active" selected>Active</option>
										<option value="inactive">Inactive</option>
									</select>
								</div>
							</div>
						</div><!-- /.box-body -->
						<div class="box-footer">
							<div class="pull-right">
								<button type="submit" class="btn bg-purple save-btn">Save</button>
								<a class="btn btn-default" href="{{route('publicPost.index')}}">Cancel</a>
							</div>
						</div><!-- /.box-footer -->
					</form>
				</div>
			</div>
		</div>
	</section>
@stop
@section('script')
@include('Admin.PublicPost.script');

@stop

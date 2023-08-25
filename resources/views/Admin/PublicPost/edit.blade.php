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
					<form  class="form-horizontal" method="post" action="{{route('publicPost.update',$publicPost->id)}}" enctype="multipart/form-data" novalidate="novalidate">
						
						@csrf
						@method('put')
						<div class="box-body">							
							<div class="form-group">
								<label for="user" class="col-sm-2 control-label">User</label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  placeholder="User" value="{{ $publicPost->user->name ?? '' }}" readonly>
								 	
								</div>
							</div>
                            <div class="form-group">
								<label for="title" class="col-sm-2 control-label">Title<span class="star_red">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  name="title" placeholder="Title" value="{{ old('title',$publicPost->title) }}">
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
												<option value="{{$category}}" @if($publicPost->category == $category) selected @endif>{{$category}}</option>
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
									<textarea  class="form-control" name="content" placeholder="Content" >{{ old('content',$publicPost->content) }}</textarea>
										
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
										<option value="myself" @if($publicPost->source == 'myself') selected @endif>Myself</option>
										<option value="external" @if($publicPost->source == 'external') selected @endif>External</option>
									</select>
								</div>
							</div>
							<div class="form-group external-section" @if($publicPost->source != 'external') style="display: none;" @endif>
								<label for="title" class="col-sm-2 control-label">External Link</label>
								<div class="col-sm-8">
									<input type="text" class="form-control"  name="external_link" placeholder="External Link" value="{{ old('external_link',$publicPost->external_link) }}">
								 	
								</div>
							</div>
							<div class="form-group">
								<label for="post_keywords" class="col-sm-2 control-label">Post Keywords</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="post_keywords"  name="post_keywords"  value="{{ old('post_keywords',$publicPost->post_keywords) }}">
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
												<option value="{{$keyword}}" @if($publicPost->moderator_keywords == $keyword) selected @endif>{{$keyword}}</option>
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
                            @if(!$publicPost->images->isEmpty())
                                <div class="form-group post-images-preview">
                                    <label for="images" class="col-sm-2 control-label">&nbsp;</label>
                                    <div class="col-sm-4">
                                            @forelse($publicPost->images as $image)
                                                @if(Storage::disk(config('constant.DISK'))->exists($image->url)) 
                                                    <div class="business_img post-images">
                                                    <i data-id="{{$image->id}}" class="fa fa-times-circle delete-post-image" aria-hidden="true"></i>
                                                        <img src="{{ Storage::disk(config('constant.DISK'))->url($image->url) }}" width="50" height="50"/>
                                                    </div>
                                                @endif                                                
                                            @empty
                                        @endforelse
                                    </div>
                                </div>
                            @endif
							<div class="form-group">
								<label for="status" class="col-sm-2 control-label">
									Status
								</label>
								<div class="col-sm-8">
									<select class="form-control"  name="status">
										<option value="">Select</option>
										<option value="draft" @if($publicPost->status == 'draft') selected @endif>Draft</option>
										<option value="active" @if($publicPost->status == 'active') selected @endif>Active</option>
										<option value="inactive" @if($publicPost->status == 'inactive') selected @endif>Inactive</option>
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

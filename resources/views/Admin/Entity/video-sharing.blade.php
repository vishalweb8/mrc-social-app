
@if($videos->isEmpty())
<input type="hidden" class="form-control video-id"  name="id[]">
<div class="form-group video-title">                            
    <label for="title" class="col-sm-2 control-label">{{trans('labels.title')}}<span class="star_red">*</span></label>
    <div class="col-sm-8">
        <input type="text" class="form-control"  name="title[]" placeholder="{{trans('labels.title')}}" value="">
    </div>
</div>

<div class="form-group">                            
    <label for="description" class="col-sm-2 control-label">{{trans('labels.description')}}</label>
    <div class="col-sm-8">
        <textarea type="text" class="form-control {{ $descClass ?? 'video-description'}}"  name="description[]" placeholder="{{trans('labels.description')}}"></textarea>
    </div>
</div>
<div class="form-group">
    <label for="thumbnail" class="col-sm-2 control-label">Thumbnail<span class="star_red">*</span></label>
    <div class="col-sm-4">
        <input type="file" name="thumbnail[]">
    </div>                            
</div>
<div class="form-group">
    <label for="video" class="col-sm-2 control-label">Video<span class="star_red">*</span></label>
    <div class="col-sm-4">
        <input type="file" name="video[]">
    </div>                            
</div>
@else    
    @foreach($videos as $video)       
        <div class="extra-video">
            <input type="hidden" class="form-control video-id"  name="id[]" value="{{$video->id}}">
            <div class="form-group video-title">                
                <label for="title" class="col-sm-2 control-label">{{trans('labels.title')}}<span class="star_red">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control"  name="title[]" placeholder="{{trans('labels.title')}}" value="{{$video->title}}">
                </div>
                @if($loop->index > 0)
                <div class="col-sm-1" style="padding-left: 0px;"> <span style="cursor: pointer;" class="badge bg-red remove-video" ><i class="fa fa-remove"></i></span></div>
                @endif
            </div>
            
            <div class="form-group">                                
                <label for="description" class="col-sm-2 control-label">{{trans('labels.description')}}</label>
                <div class="col-sm-8">
                <textarea type="text" class="form-control {{ $descClass ?? 'video-description'}}"  name="description[]" placeholder="{{trans('labels.description')}}">{{$video->description}}</textarea>
                </div>
            </div>
            <div class="form-group video-thumbnail">
                <label for="thumbnail" class="col-sm-2 control-label">Thumbnail</label>
                <div class="col-sm-4">
                    <input type="file"  name="thumbnail[]">
                </div>                            
            </div>
            @if(!empty($video))
                <div class="form-group">
                    <label for="media_images" class="col-sm-2 control-label">&nbsp;</label>
                    <div class="col-sm-8">
                        @if($video->thumbnail != '' && Storage::disk(config('constant.DISK'))->exists($video->thumbnail))
                            <img src="{{ Storage::disk(config('constant.DISK'))->url($video->thumbnail) }}" width="50" height="50"/>
                        @else
                            <img src="{{ url(Config::get('constant.DEFAULT_IMAGE')) }}" width="50" height="50"/>
                        @endif
                    </div>
                </div>
            @endif
            <div class="form-group">
                <label for="video" class="col-sm-2 control-label">Video</label>
                <div class="col-sm-4">
                    <input type="file" name="video[]">
                </div>
                @if(!empty($video->video_url))
                <div class="col-sm-4">
                    <a href="{{$video->video_url}}" target="_blank">{{$video->title}}</a>
                </div>
                @endif                    
            </div>
        </div>
    @endforeach
@endif
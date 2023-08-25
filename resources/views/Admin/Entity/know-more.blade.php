@php
    $languages = trim(Helpers::isOnSettings('other_languages'));
    $othersLangs = [];
    if(!empty($languages)) {
        $othersLangs = explode(',',$languages);
    }
@endphp
@if($knowMores->isEmpty())
<div class="form-group">                                    
    <label for="title" class="col-sm-2 control-label">{{trans('labels.language')}}</label>
    <div class="col-sm-8">
        <select name="language" id="{{ $langId ?? 'knowMoreLang'}}" class="form-control" >
            <option value="english" {{($selectedLanguage == 'english')?'selected':''}}>English</option>
            @foreach($othersLangs as $language)
                <option value="{{$language}}" {{($selectedLanguage == $language)?'selected':''}}>{{$language}} </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group know-more-title">                            
    <label for="title" class="col-sm-2 control-label">{{trans('labels.title')}}<span class="star_red">*</span></label>
    <div class="col-sm-8">
        <input type="text" class="form-control"  name="title[]" placeholder="{{trans('labels.title')}}" value="">
    </div>
</div>

<div class="form-group">                            
    <label for="description" class="col-sm-2 control-label">{{trans('labels.description')}}</label>
    <div class="col-sm-8">
        <textarea type="text" class="form-control {{ $descClass ?? 'know-more-description'}}"  name="description[]" placeholder="{{trans('labels.description')}}"></textarea>
    </div>
</div>
@else    
    @foreach($knowMores as $knowMore)
        @if($loop->first)
        <div class="form-group">                                    
            <label for="title" class="col-sm-2 control-label">{{trans('labels.language')}}</label>
            <div class="col-sm-8">
                <select name="language" id="{{ $langId ?? 'knowMoreLang'}}" class="form-control" >
                    <option value="english" selected>English</option>
                    @foreach($othersLangs as $language)
                        <option value="{{$language}}" {{($knowMore->language == $language)?'selected':''}}>{{$language}} </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif
        <div class="extra-know-more">
            <div class="form-group know-more-title">
                
                <label for="title" class="col-sm-2 control-label">{{trans('labels.title')}}<span class="star_red">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control"  name="title[]" placeholder="{{trans('labels.title')}}" value="{{$knowMore->title}}">
                </div>
                @if($loop->index > 0)
                <div class="col-sm-1" style="padding-left: 0px;"> <span style="cursor: pointer;" class="badge bg-red remove-know-more" ><i class="fa fa-remove"></i></span></div>
                @endif
            </div>
            
            <div class="form-group">                                
                <label for="description" class="col-sm-2 control-label">{{trans('labels.description')}}</label>
                <div class="col-sm-8">
                <textarea type="text" class="form-control {{ $descClass ?? 'know-more-description'}}"  name="description[]" placeholder="{{trans('labels.description')}}">{{$knowMore->description}}</textarea>
                </div>
            </div>
        </div>
    @endforeach
@endif
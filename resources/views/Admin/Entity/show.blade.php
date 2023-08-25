@extends('Admin.Master')
@section('content')
<link type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
@php 
$rowClass = 'row';
use Illuminate\Support\Str;
$strLimit = 300;
$notAvailable = "N/A";
$allEntities = getEntities();
@endphp
<section class="content-header">
    <h1>
        View  {{trans('labels.entity')}}
    </h1>    
</section>
<!-- Main content -->
<section class="content">    
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{trans('labels.entity')}} Information</h3>
                    <a class="pull-right" href="{{ getUrlOfEntityDetail($entity) }}" target="_blank" title="Permalink">
                        Permalink
                    </a>
                </div><!-- /.box-header -->
                    <div class="box-body">

                        <div class="{{$rowClass}}">                            
                            <label class="col-sm-2 control-label">{{trans('labels.name')}}</label>
                            <div class="col-sm-8">
                                {{ $entity->name ?? ''}}
                            </div>
                        </div>
                        <div class="{{$rowClass}}">                            
                            <label  class="col-sm-2 control-label">{{trans('labels.entity_type')}}</label>
                            <div class="col-sm-8">
                                {{ $entity->entityType->name ?? ''}}
                            </div>
                        </div>
                        @if(isset($entity->user))
                        <div class="{{$rowClass}}">
                            
                            <label for="business_user" class="col-sm-2 control-label"> {{trans('labels.user')}}</label>
                            <div class="col-sm-8">
                            {{ $entity->user->name ?? ''}}
                            </div>
                        </div>
                        @endif
                        <div class="{{$rowClass}}">                            
                            <label for="short_description" class="col-sm-2 control-label">{{trans('labels.short_description')}}</label>
                            <div class="col-sm-8">
                                <div class="short-desc">
                                    {!! $entity->short_description !!}
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <a href="#" id="showOtherLangShortDesc">view in other language</a></br>
                            </div>  
                        </div>
                        
                        <div class="{{$rowClass}}">                            
                            <label for="description" class="col-sm-2 control-label">{{trans('labels.description')}}</label>
                            <div class="col-sm-8">
                                <div class="short-desc">
                                {!! $entity->description !!}
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <a href="#" id="showOtherLang">view in other language</a></br>
                                <a href="#" class="showSuggestions" data-id="{{$entity->id}}" data-type="entity">view suggestions</a>
                            </div>                            
                        </div>

                        <div class="{{$rowClass}}">                            
                            <label for="email_id" class="col-sm-2 control-label">
                                {{trans('labels.email')}}
                             </label>
                            <div class="col-sm-8">
                                {{ $entity->email ?? $notAvailable}}
                            </div>
                        </div>
                        @if(!empty($entity->businessImages))
                            <div class="{{$rowClass}}">
                                <label class="col-sm-2 control-label">Images</label>
                                <div class="col-sm-8">                                    
                                    @forelse($entity->businessImages as $image)
                                        @if(Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$image['image_name'])) 
                                        <div class="business_img" id="business_img_{{$image['id']}}">
                                            
                                            <img src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$image['image_name']) }}" width="50" height="50"/>
                                        </div>
                                        @endif                                                
                                    @empty
                                    @endforelse
                                </div>
                            </div>
                        @endif
                        <div class="{{$rowClass}}">
                            <label for="business_logo" class="col-sm-2 control-label">{{trans('labels.logo')}}</label>
                            <div class="col-sm-8">
                                @if($entity->business_logo != '' && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$entity->business_logo))
                                    <img src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$entity->business_logo) }}" width="50" height="50"/>
                                @else
                                    <img src="{{ url(Config::get('constant.DEFAULT_IMAGE')) }}" width="50" height="50"/>
                                @endif
                            </div>
                        </div>
                        <div class="{{$rowClass}}">                            
                            <label for="website_url" class="col-sm-2 control-label">{{trans('labels.establishmentyear')}}</label>
                            <div class="col-sm-8">
                                {{ ($entity->establishment_year != 0) ? $entity->establishment_year : $notAvailable}}
                            </div>
                        </div>
                        <div class="{{$rowClass}}">                            
                            <label class="col-sm-2 control-label">SEO Meta tags</label>
                            <div class="col-sm-8">
                                {{ $entity->seo_meta_tags ?? $notAvailable}}
                            </div>
                        </div>

                        <div class="{{$rowClass}}">                            
                            <label class="col-sm-2 control-label">SEO Meta description</label>
                            <div class="col-sm-8">
                                {{ $entity->seo_meta_description ?? $notAvailable}}
                            </div>
                        </div>

                        <div class="{{$rowClass}}">
                            <label for="promoted" class="col-sm-2 control-label">{{trans('labels.promoted')}}</label>
                            <div class="col-sm-8">
                                @if($entity->promoted == 1)
                                    Promoted
                                @else
                                    Not Promoted
                                @endif
                            </div>
                        </div>
                        <div class="{{$rowClass}}">
                            <label for="is_normal_view" class="col-sm-2 control-label">Front-End View</label>
                            <div class="col-sm-8">
                                @if($entity->is_normal_view == 1)
                                    Normal View
                                @else
                                    Extended View
                                @endif
                            </div>
                        </div>

                        <div class="{{$rowClass}}">
                            <label for="approved" class="col-sm-2 control-label">{{trans('labels.status')}}</label>
                            <div class="col-sm-8">
                                @if($entity->approved == 1)
                                    Approved
                                @elseif($entity->approved == 2)
                                    Rejected
                                @else
                                    Not Approved
                                @endif
                            </div>
                        </div>

                        <div class="{{$rowClass}}">
                            <label for="membership_type" class="col-sm-2 control-label">{{trans('labels.membershiptype')}}</label>
                            <div class="col-sm-8">
                                @if($entity->membership_type == 1)
                                    Premium
                                @elseif($entity->membership_type == 2)
                                    Lifetime
                                @else
                                    Basic
                                @endif
                            </div>
                        </div>
                        <div class="{{$rowClass}}">
                            <label for="document_approval" class="col-sm-2 control-label">Document Approval </label>
                            <div class="col-sm-8">
                                @if($entity->document_approval == 1)
                                    1st level
                                @elseif($entity->document_approval == 2)
                                    2nd level
                                @elseif($entity->document_approval == 3)
                                    3rd level
                                @else
                                    Not approved
                                @endif
                            </div>
                        </div>

                    </div><!-- /.box-body -->
            </div>
            @if(isset($enableComponent->know_more) && !$entity->knowMores->isEmpty())
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Know more</h3>
                    <div class="col-sm-2 pull-right">
                        <a href="#" class="showKnowOtherLang">view in other language</a>
                    </div>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    @foreach($entity->knowMores as $knowMore)
                        <div class="{{$rowClass}}">                            
                            <label  class="col-sm-2 control-label">{{trans('labels.title')}}</label>
                            <div class="col-sm-8">
                                {!! Str::limit($knowMore->title,$strLimit) !!}
                            </div>                            
                        </div>
                        <div class="{{$rowClass}}">                            
                            <label  class="col-sm-2 control-label">{{trans('labels.description')}}</label>
                            <div class="col-sm-8">
                                <div class="short-desc">
                                    {!! $knowMore->description !!}
                                </div>
                            </div>
                            <a href="#" class="showSuggestions" data-id="{{$knowMore->id}}" data-type="know">view suggestions</a>
                        </div>
                    @endforeach
                </div><!-- /.box-body -->
            </div>
            <div class='modal modal-centered fade' id='knowMoreModal' role='dialog'>
                <div class='modal-dialog modal-dialog-centered'>
                    <div class='modal-content'>
                        <form  method="POST" action="#">
                            <div class='modal-header'>
                                <h3>Know more in multiple languages<button type='button' class='close pull-right' data-dismiss='modal'>&times;</button></h3>                    
                            </div>
                            <div class='modal-body'>
                                <div class="{{$rowClass}}">                            
                                    <label  class="col-sm-2 control-label">{{trans('labels.language')}}</label>
                                    <label  class="col-sm-2 control-label">{{trans('labels.title')}}</label>
                                    <label  class="col-sm-6 control-label">{{trans('labels.description')}}</label>
                                </div>
                                @forelse($knowMoresInOtherLang as $other)
                                    <div class="{{$rowClass}}">                            
                                        <div class="col-sm-2">
                                            {!! $other->language !!}
                                        </div>
                                        <div class="col-sm-2">
                                            {!! $other->title !!}
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="short-desc">
                                            {!! $other->description !!}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                <div class="{{$rowClass}}">                            
                                    <div class="col-sm-8">
                                        Data not available
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
            @if(isset($enableComponent->contact_details))
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Contact Details</h3>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.landline')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->phone ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.mobile')}}</label>
                        <div class="col-sm-8">
                            @if(!empty($entity->country_code)) ({{ $entity->country_code }}) @endif   {{ $entity->mobile ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.address')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->address ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.latitude')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->latitude ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.longitude')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->longitude ?? $notAvailable}}
                        </div>
                    </div>                    
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.streetaddress')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->street_address ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.locality')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->locality ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.country')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->country ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.state')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->state ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.district')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->district ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.city')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->city ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.taluka')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->taluka ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.pincode')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->pincode ?? $notAvailable}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">{{trans('labels.websiteurl')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->website_url ?? $notAvailable}}
                        </div>
                    </div>

                </div><!-- /.box-body -->
            </div>
            @endif
            @if(isset($enableComponent->category))
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{trans('labels.lblcategories')}}</h3>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">{{trans('labels.lblcategories')}}</label>
                        <div class="col-sm-8">
                        @if(!empty($entity->category_id))
                            <?php  $explodeCategories = explode(',',$entity->category_id); ?>
                            @foreach($explodeCategories as $category)
                                @if($category > 0)
                                    <?php  $categories = Helpers::getCategoryReverseHierarchy($category); ?><br>
                                    @if(!empty($categories))
                                        <?php
                                            $tempArr = [];
                                            foreach($categories as $categoryTemp){
                                                $tempArr[] = '<span class="label label-success">'.$categoryTemp['name'].'</span>';
                                            }

                                            echo implode(" > ", array_reverse($tempArr));
                                        ?>
                                    @endif
                                @endif
                            @endforeach
                        @endif
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">Metatags</label>
                        <div class="col-sm-8">
                            @php
                            $explodetags = [];
                            if(!empty($entity->metatags)) {
                                $explodetags = array_filter(explode(',',$entity->metatags));
                            }
                            @endphp
                            @forelse($explodetags as $tags)
                                <span class="label label-success">{{$tags}}</span>
                            @empty
                                -
                            @endforelse
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div>
            @endif
            @if(isset($enableComponent->hours_of_opration) && isset($entity->businessWorkingHours))
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{trans('labels.hoursofoperationtimezone')}}</h3>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">Timezone</label>
                        <div class="col-sm-8">
                            {!! $entity->businessWorkingHours->timezone ?? $notAvailable !!}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">
                        <div class="col-sm-8">
                            <table class="table open_hours_table" border=0>
                                <tr style="font-weight: bold;">
                                    <td style="width: 100px;">Days</td>
                                    <td style="width: 100px;">Open/Close</td>
                                    <td style="width: 150px;">Start time</td>
                                    <td style="width: 150px;">End time</td>
                                </tr>
                                <?php 
                                    $days = Helpers::getWeekDays();
                                    $times = Helpers::getTime(); 
                                ?>
                                @foreach($days as $day)
                                <?php 
                                    $day_start_time = $day.'_start_time';
                                    $day_end_time = $day.'_end_time';
                                    $day_open_close = $day.'_open_close';

                                    if (isset($data) && isset($data->businessWorkingHours->$day_start_time) && isset($data->businessWorkingHours->$day_end_time) && isset($data->businessWorkingHours->$day_open_close))
                                    {
                                        
                                        $day_start_time = $data->businessWorkingHours->$day_start_time;
                                        $day_time = Helpers::getReverseServerDateTimeIntoGivenTimezone($day_start_time,$data->businessWorkingHours->timezone);
                                        $day_start_time = $day_time['time'];
                                        $day_start_am_pm = $day_time['am_pm'];

                                        
                                        
                                        $day_end_time = $data->businessWorkingHours->$day_end_time;
                                        $day_time = Helpers::getReverseServerDateTimeIntoGivenTimezone($day_end_time,$data->businessWorkingHours->timezone);        
                                        $day_end_time =  $day_time['time'];
                                        $day_end_am_pm = $day_time['am_pm'];

                                        $day_open_close = $data->businessWorkingHours->$day_open_close;
                                    }
                                    else{
                                        $day_start_time = '9:00';
                                        $day_start_am_pm = '';

                                        $day_end_time = '6:00';
                                        $day_end_am_pm = 'PM';

                                        $day_open_close = '';
                                    }

                                ?>
                                <tr>
                                    <td>{{ucfirst($day)}}</td>
                                    <td>
                                        <select name="{{$day}}_open_close" disabled>
                                            <option value="1" {{($day_open_close == '1')?'selected':''}}>Open</option>
                                            <option value="0" {{($day_open_close == '0')?'selected':''}}>Close</option>
                                        </select>
                                    </td>
                                    
                                    <td class="{{$day}}_start_time"  style={{($day_open_close == Config::get('constant.BUSINESS_WORKING_CLOSE_FLAG')) ? 'display:none;' : ''}}>
                                        <select name="{{$day}}_start_time" style="width: 80px;" disabled>
                                            @foreach($times as $time)
                                                <option value="{{$time}}" {{($day_start_time == $time) ? 'selected' : ''}}>{{$time}}</option>
                                            @endforeach
                                        </select>
                                        <select name="{{$day}}_start_time_am_pm" disabled>
                                            <option value="AM" {{($day_start_am_pm == 'AM')?'selected':''}}>AM</option>
                                            <option value="PM" {{($day_start_am_pm == 'PM')?'selected':''}}>PM</option>
                                        </select>
                                    </td>
                                    <td class="{{$day}}_end_time" style={{($day_open_close == Config::get('constant.BUSINESS_WORKING_CLOSE_FLAG')) ? 'display:none;' : ''}}>
                                        <select name="{{$day}}_end_time" style="width: 80px;" disabled>
                                            @foreach($times as $time)
                                                <option value="{{$time}}" {{($day_end_time == $time) ? 'selected' : ''}}>{{$time}}</option>
                                            @endforeach
                                        </select>
                                        <select name="{{$day}}_end_time_am_pm" disabled>
                                            <option value="AM" {{($day_end_am_pm == 'AM')?'selected':''}}>AM</option>
                                            <option value="PM" {{($day_end_am_pm == 'PM')?'selected':''}}>PM</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div>
            @endif
            @if(isset($enableComponent->business_activities) && !$entity->businessActivities->isEmpty())
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Business Activities</h3>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-3 control-label">{{trans('labels.title')}}</label>
                        <label  class="col-sm-3 control-label">Activity date</label>
                    </div>
                    @foreach($entity->businessActivities as $activity)
                        <div class="{{$rowClass}}">
                            <div class="col-sm-3">
                                {!! $activity->activity_title !!}
                            </div>
                            <div class="col-sm-3">
                                {!! $activity->activity_date ?? $notAvailable !!}
                            </div>
                        </div>
                    @endforeach
                </div><!-- /.box-body -->
            </div>
            @endif
            @if(isset($enableComponent->business_documnet) && !$entity->BusinessDoc->isEmpty())
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Business Documents</h3>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    @foreach($entity->BusinessDoc as $val)
                        <div class="{{$rowClass}}">
                            <div class="col-sm-4">
                               <label>
                                   {{ $val->doc_name}}
                               </label>  
                            </div>
                            <div class="col-sm-4">
                                <a href="{{url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$val->front_image)}}" download="download"><img src="{{url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$val->front_image)}}" style="width: 100px"></a>
                            </div>
                            <div class="col-sm-4">
                                <a href="{{url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$val->back_image)}}" download="download"><img src="{{url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$val->back_image)}}" style="width: 100px">
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div><!-- /.box-body -->
            </div>
            @endif
            @if(isset($enableComponent->near_by) && !$entity->nearByFilter->isEmpty())
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Near By</h3>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                @foreach($entity->nearByFilter as $nearByFilter)
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">Is Enable Filters</label>
                        <div class="col-sm-8">
                            <label class="switch">
                                <input type="checkbox"   autocomplete="off" value="1" @if(isset($nearByFilter) && $nearByFilter->is_enable_filter) checked @endif disabled>
                                <span class="slider round" style="cursor: default;"></span>
                            </label>
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">{{trans('labels.title')}}</label>
                        <div class="col-sm-8">
                            {!! $nearByFilter->title !!}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">Top limit</label>
                        <div class="col-sm-8">
                            {!! $nearByFilter->top_limit !!}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">{{trans('labels.entities')}}</label>
                        <div class="col-sm-8">
                        <?php
                            $entity_types = [];
                            $entityNames = '';
                            if (!empty($nearByFilter->asset_type_id)) {
                                $entity_types = explode(",",$nearByFilter->asset_type_id);
                                foreach($allEntities as $entityType) {
                                    if(in_array($entityType->id,$entity_types)) {
                                        $entityNames .= $entityType->name.', ';
                                    }
                                }
                            }
                        ?>
                        {{rtrim($entityNames,', ')}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">Sql Query</label>
                        <div class="col-sm-8">
                            {!! $nearByFilter->sql_query !!}
                        </div>
                    </div>
                    @endforeach
                </div><!-- /.box-body -->
            </div>
            @endif
            @if(isset($enableComponent->social_profiles))
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Social Profiles</h3>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">{{trans('labels.facebook')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->facebook_url ?? $notAvailable }}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">{{trans('labels.twitter')}}</label>
                        <div class="col-sm-8">
                        {{ $entity->twitter_url ?? $notAvailable }}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">{{trans('labels.linkedin')}}</label>
                        <div class="col-sm-8">
                        {{ $entity->linkedin_url ?? $notAvailable }}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">{{trans('labels.instagram')}}</label>
                        <div class="col-sm-8">
                        {{ $entity->instagram_url ?? $notAvailable }}
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div>
            @endif
            @if(isset($enableComponent->online_stores) && !empty($entity->online_store_url))
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Online Stores</h3>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-3 control-label">Stores</label>
                        <label  class="col-sm-3 control-label">{{trans('labels.online_store_url')}}</label>
                    </div>
                    @php 
                        $onlineStores = json_decode($entity->online_store_url);
                    @endphp
                    @foreach($onlineStores as $onlineStore)
                        <div class="{{$rowClass}}">
                            <div class="col-sm-3">
                            @php
                                $store = $stores->where('id',$onlineStore->id)->first();
                            @endphp
                            @if($store)
                                {{ $store->name }}
                            @endif
                            </div>
                            <div class="col-sm-3">
                                {{$onlineStore->url}}
                            </div>
                        </div>
                    @endforeach
                </div><!-- /.box-body -->
            </div>
            @endif
            @if(isset($enableComponent->public_website))
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Public Website</h3>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">{{trans('labels.urlslug')}}</label>
                        <div class="col-sm-8">
                            {{ $entity->url_slug ?? $notAvailable }}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label  class="col-sm-2 control-label">{{trans('labels.websitecolortheme')}}</label>
                        <div class="col-sm-8">
                        {{ $entity->web_site_color_theme ?? $notAvailable }}
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div>
            @endif
            @if(isset($enableComponent->custom_component) && !$entity->customDetails->isEmpty())
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Custom Details</h3>
                    <div class="col-sm-2 pull-right">
                        <a href="#" class="showCustomDetailOtherLang">view in other language</a>
                    </div>
                </div><!-- /.box-header -->                    
                <div class="box-body">
                    @foreach($entity->customDetails as $customDetail)
                        <div class="{{$rowClass}}">                            
                            <label  class="col-sm-2 control-label">{{trans('labels.title')}}</label>
                            <div class="col-sm-8">
                                {!! Str::limit($customDetail->title,$strLimit) !!}
                            </div>
                        </div>
                        <div class="{{$rowClass}}">                            
                            <label  class="col-sm-2 control-label">{{trans('labels.description')}}</label>
                            <div class="col-sm-8">
                                <div class="short-desc">
                                    {!! $customDetail->description !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div><!-- /.box-body -->
            </div>
            <div class='modal modal-centered fade' id='customDetailModal' role='dialog'>
                <div class='modal-dialog modal-dialog-centered'>
                    <div class='modal-content'>
                        <form  method="POST" action="#">
                            <div class='modal-header'>
                                <h3>Custom Details in multiple languages<button type='button' class='close pull-right' data-dismiss='modal'>&times;</button></h3>                    
                            </div>
                            <div class='modal-body'>
                                <div class="{{$rowClass}}">                            
                                    <label  class="col-sm-2 control-label">{{trans('labels.language')}}</label>
                                    <label  class="col-sm-2 control-label">{{trans('labels.title')}}</label>
                                    <label  class="col-sm-6 control-label">{{trans('labels.description')}}</label>
                                </div>
                                @forelse($customDetailsInOtherLang as $other)
                                    <div class="{{$rowClass}}">                            
                                        <div class="col-sm-2">
                                            {!! $other->language !!}
                                        </div>
                                        <div class="col-sm-2">
                                            {!! $other->title !!}
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="short-desc">
                                            {!! $other->description !!}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                <div class="{{$rowClass}}">                            
                                    <div class="col-sm-8">
                                        Data not available
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
            <!-- <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Reports</h3>
                </div>                    
                <div class="box-body">
                    <div class="box-body table-responsive">
                        <table class="table table-bordered" id="reports-table" role="grid"></table>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
    <div class='modal modal-centered fade' id='descModal' role='dialog'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <form  method="POST" action="#">
                    <div class='modal-header'>
                        <h3>Description in multiple language<button type='button' class='close pull-right' data-dismiss='modal'>&times;</button></h3>                    
                    </div>
                    <div class='modal-body'>
                        <div class="{{$rowClass}}">                            
                            <label  class="col-sm-2 control-label">{{trans('labels.language')}}</label>
                            <label  class="col-sm-10 control-label">{{trans('labels.description')}}</label>
                        </div>
                        @forelse($entity->otherLangDescriptions as $other)
                            <div class="{{$rowClass}}">                            
                                <div class="col-sm-2">
                                    {!! $other->language !!}
                                </div>
                                <div class="col-sm-8">
                                    <div class="short-desc">
                                    {!! $other->description!!}
                                    </div>
                                </div>
                            </div>
                        @empty
                        <div class="{{$rowClass}}">                            
                            <div class="col-sm-8">
                                Data not available
                            </div>
                        </div>
                        @endforelse
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class='modal modal-centered fade' id='shortDescModal' role='dialog'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <form  method="POST" action="#">
                    <div class='modal-header'>
                        <h3>Short description in multiple language<button type='button' class='close pull-right' data-dismiss='modal'>&times;</button></h3>                    
                    </div>
                    <div class='modal-body'>
                        <div class="{{$rowClass}}">                            
                            <label  class="col-sm-2 control-label">{{trans('labels.language')}}</label>
                            <label  class="col-sm-10 control-label">{{trans('labels.description')}}</label>
                        </div>
                        @forelse($entity->otherLangDescriptions as $other)
                            <div class="{{$rowClass}}">                            
                                <div class="col-sm-2">
                                    {!! $other->language !!}
                                </div>
                                <div class="col-sm-8">
                                    <div class="short-desc">
                                    {!! $other->short_description !!}
                                    </div>
                                </div>
                            </div>
                        @empty
                        <div class="{{$rowClass}}">                            
                            <div class="col-sm-8">
                                Data not available
                            </div>
                        </div>
                        @endforelse
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class='modal modal-centered fade' id='suggestionModal' role='dialog'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h3>Suggestions of description<button type='button' class='close pull-right' data-dismiss='modal'>&times;</button></h3>                    
                </div>
                <div class='modal-body'>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered" id="description-table" role="grid"></table>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="entity_id" value="{{$entity->id}}" />
        <input type="hidden" id="entity_type" value="entity" />
    </div>
</section><!-- /.content -->
@stop
@section('script')
<script>
    $(document).ready(function () {
        $("#showOtherLang").click(function() {
            $("#descModal").modal('show');
        });
        $("#showOtherLangShortDesc").click(function() {
            $("#shortDescModal").modal('show');
        });
        $(".showKnowOtherLang").click(function() {
            $("#knowMoreModal").modal('show');
        });
        
        $(".showCustomDetailOtherLang").click(function() {
            $("#customDetailModal").modal('show');
        });   

        $('#description-table').DataTable( {
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            //searching: false,
            //ordering: false,
            ajax: { 
                url: "{{ route('entity.desc.suggestion') }}",
                data: function(data) {
                    data.type = $("#entity_type").val();
                    data.entity_id = $("#entity_id").val();
                }
            },
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id', title: 'Id'},
                {data: 'user.name', name: 'user.name', title: 'Suggest By'},
                {data: 'description', name: 'description', title: 'Suggestion'},
                {data: 'created_at', name: 'created_at', title: 'Suggested At'}
            ]
        });

        // $('#reports-table').DataTable( {
        //     hideEmptyCols: true,
        //     processing: true,
        //     serverSide: true,
        //     ajax: { 
        //         url: "{{ route('entity.reports') }}",
        //         data: function(data) {
        //             data.entity_id = $("#entity_id").val();
        //         }
        //     },
        //     aaSorting: [[0, 'desc']],
        //     columns: [
        //         {data: 'id', name: 'id', title: 'Id'},
        //         {data: 'report_by.name', name: 'reportBy.name', title: 'Report By'},
        //         {data: 'comment', name: 'comment', title: 'Comment', orderable:false},
        //         {data: 'created_at', name: 'created_at', title: 'Reported At'},
        //         {data: 'action', name: 'action', title: 'Action',orderable:false, searchable:false}
        //     ]
        // });

        $(".showSuggestions").click(function() {
            $('#entity_type').val($(this).data('type'));
            $('#entity_id').val($(this).data('id'));
            $('#description-table').DataTable().ajax.reload();
            $("#suggestionModal").modal('show');
        });
    });
</script>
@stop

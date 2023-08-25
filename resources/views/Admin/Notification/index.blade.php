@extends('Admin.Master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css">
<link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.tag-editor.css') }}" />
<!-- Content Header (Page header) -->
@canany([config('perm.sendNotification'),config('perm.saveNotification')])
<section class="content-header">
    <h1>
        {{trans('labels.branding_notifications')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                    <strong>{{trans('labels.whoops')}}</strong> {{trans('labels.someproblems')}}<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="box">
                <form id="sendPushNotification" method="post" action="{{ route('send.notifications') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="user_id" value="{{ \Auth::id() }}">

                    <div class="box-body">

                       <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="title" class="col-form-label">{{trans('labels.title')}}<span class="star_red">*</span></label>
                                <input type="text" class="form-control" id="title" name="title">
                            </div>

                            <div class="form-group">
                                <label for="external_link" class="col-form-label">External Link</label>
                                <input type="text" class="form-control" name="external_link" id="external_link">
                            </div>
                            <div class="form-group">
                                <label for="target_link" class="col-form-label">Target Type</label>
                                <a href='#' data-html="true" data-toggle="popover"  data-trigger="hover"  title="Target Type" data-content="<h4>Use following target type</h4><ol>	
                                        <li>member-profile </li>
                                        <li>owner-profile </li>
                                        <li>business-profile </li>
                                        <li>marketplace </li>
                                    </ol>" style="color:black"><i class="fa fa-info-circle fa-2x" aria-hidden="true"></i>
                                </a>
                                <input type="text" class="form-control" name="target_link" id="target_link">
                            </div>
                       </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="description" class="col-form-label">{{ trans('labels.description') }}<span class="star_red">*</span></label>
                                <textarea name="description" class="form-control c-desc-box" id="description" cols="94" rows="8"></textarea>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->

 
<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">
                <div class="content">
                    <div class="radio-processed-content">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="radio-content">
                                <div class="form-check form-check-inline bg-light-radio">
                                    <input class="form-check-input" type="radio" name="sendNotification" id="sendToAll" value="sendToAll" checked>
                                    <label class="form-check-label" for="sendToAll">
                                        Send to all
                                    </label>
                                </div>
                                <div class="form-check form-check-inline bg-light-radio">
                                    <input class="form-check-input" type="radio" name="sendNotification" id="targetSpecficAudience" value="targetSpecficAudience">
                                    <label class="form-check-label" for="targetSpecficAudience">
                                        Target specific audience
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 send-to-all-section">
                            <div class="radio-content">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="notificationType" id="sendToAllBusiness" value="all_business">
                                    <label class="form-check-label" for="sendToAllBusiness">
                                        Send to all businesses
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="notificationType" id="sendToAllMember" value="all_member">
                                    <label class="form-check-label" for="sendToAllMember">
                                        Send to all members
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="notificationType" id="sendToAllEveryone" value="all" checked>
                                    <label class="form-check-label" for="sendToAllEveryone">
                                        Send to everyone
                                    </label>
                                </div>
                            </div>
                            <!-- <div class="send-radio-content">
                                <button class="btn btn-light">Send to all business</button>
                                <button class="btn btn-light">Send to all members</button>
                                <button class="btn btn-light">Send to all everyone</button>
                            </div> -->
                        </div>
                        <div class="col-sm-12 filter-form-wrapper">
                            <div class="radio-content">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="notificationType" id="targetBusiness" value="target_business">
                                    <label class="form-check-label" for="targetBusiness">
                                        Select from businesses
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="notificationType" id="targetMember" value="target_member">
                                    <label class="form-check-label" for="targetMember">
                                        Select from members
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="notificationType" id="targetEveryone" value="target_all">
                                    <label class="form-check-label" for="targetEveryone">
                                        Select from everyone
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 send-to-all-section">
                            <div class="results-found">
                                <h5 class="title">
                                <span class="filters-count">0</span> Total results
                                </h5>
                                @can(config('perm.sendNotification'))
                                    <button class="btn bg-purple" status="pending" id="send-notification-btn-all">Continue to send</button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="filter-form-wrapper">
                    <form id="filter-form" action="#">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-4">
                                <select name="category_id" id="category" class="form-control  select2" multiple>
                                    <option value="">Select Category </option>
                                    @foreach($categories  as $cat) 
                                        <option value="{{$cat->id}}" >{{$cat->name}} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-4">
                                <select name="sub_category_id" id="sub_category" class="form-control  select2" multiple>                                
                                    <option value="">Select Sub Category</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt15">
                            <div class="col-sm-12 col-md-6 mb-4">
                                <select name="business_id" id="business_id" class="form-control select2" multiple>
                                    <option value="">Select Business </option>
                                </select>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-4 member-section">
                                <select name="user_id" id="user_name" class="form-control select2" multiple>
                                    <option value="">Select User</option>
                                </select>
                            </div>
                        </div>                     
                        <div class="row mt15">
                            <div class="col-sm-12 col-md-6 mb-4 member-section">
                                <select name="country" id="country" class="form-control select2" multiple>
                                    <option value="">Select Country</option>
                                    @foreach($countries as $val) 
                                    <option value="{{$val->name}}" >{{$val->name}} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-4 member-section">
                                <select name="state" id="state" class="form-control select2" multiple>
                                    
                                    <option value="">Select state </option>

                                    @foreach($states as $val2) 
                                        <option value="{{$val2->name}}" >{{$val2->name}} </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>
                        <div class="row mt15">
                            <div class="col-sm-12 col-md-6 mb-4 member-section">
                                <select name="district" id="district" class="form-control select2" multiple>
                                    <option value="">Select District</option>
                                    @foreach($districts as $val2) 
                                        <option value="{{$val2->district}}">{{$val2->district}} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-4 member-section">
                                <select name="city" id="city" class="form-control select2"  multiple>                        
                                    <option value="">Select City</option>
                                    @foreach($cities as $val3) 
                                    <option value="{{$val3->name}}" >{{$val3->name}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>                        
                        <div class="row mt15">
                            <div class="col-sm-12 col-md-6 mb-4 member-section">
                                <select name="gender" id="gender" class="form-control select2">
                                    <option value="">Select Gender</option>
                                    <option value="1">Male</option>
                                    <option value="2">Female</option>
                                </select>
                            </div>
                            
                            <div class="col-sm-12 col-md-6 mb-4 member-section">
                                <select name="education" id="education_quilification" class="form-control select2" multiple>
                                    <option value="">Select Education Qualifications</option>
                                    @foreach($educations as $education) 
                                    <option value="{{$education->education}}" >{{$education->education}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>                   
                        <div class="row mt15">
                            <div class="col-sm-12 col-md-6 mb-4">
                                <select name="membership_type" id="verification_level" class="form-control select2">                        
                                    <option value="">Select Verification Level</option>
                                    <option value="0">Basic Business</option>
                                    <option value="1">Premium Business</option>
                                    <option value="2">Lifetime Business</option>
                                </select>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-4">
                                <select name="caste" id="caste" class="form-control select2" multiple>                        
                                    <option value="">Select Caste/Samaj</option>
                                    @foreach($castes as $caste) 
                                    <option value="{{$caste->kul_gotra}}" >{{$caste->kul_gotra}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt15">
                            <div class="col-sm-12 col-md-6">
                                <select class="form-control select2"  id="categoty_tags" >
                                    <option value="">Select Metatags</option>
                                </select>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <input type="text" class="form-control" id="metatags" name="meta_tags" value="">
                            </div>                            
                        </div>
                        <div class="row mt15">
                            <div  class="col-sm-12 mt15">
                                <div class="age-group">
                                    <h5 class="title">Select age groups</h5> 
                                    <a href='#'  data-toggle="popover"  data-trigger="hover"  title="Info" data-content="Select the minimum and maximum age of the people who will find your notification relevant. You can add maximum 3 age groups" style="color:black"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-4 member-section">
                                Select minimum age
                                <input  type="range" min="1" max="100" value="1" id="minAge" />
                                Selected value: <span id="showMinAge">1</span>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-4 member-section">
                                Select maximum age
                                <input  type="range"  min="1" max="100" value="80" id="maxAge" />
                                Selected value: <span id="showMaxAge">80</span>
                            </div>
                            <div class="col-sm-12 col-md-3 mb-4 mt15">
                                <button type="button" id="addAgeGroup" class="btn "> Add Age Group </button>
                            </div>
                            <div class="col-sm-12 col-md-3 mb-4 mt15" style="text-align:right;padding-top:10px">
                                Selected Age Groups:
                            </div>
                            <div class="col-sm-12 col-md-6 mt15 member-section">
                                <input type="text" class="form-control" id="age_groups" name="age_groups" value="">
                            </div>
                            <div  class="col-sm-12 mt15">
                                <button id="clear-btn" type="button" class="btn save-btn">Clear</button>
                                <button id="filter-user-btn" type="button" class="btn bg-purple save-btn">Search</button>
                            </div>
                        </div> 
                    </form>
                    </div>

                </div>
                
            </form>
            </div>
        </div>
    </div>
</section>

<section class="content-header filter-form-wrapper">
    <h1>
        Results based on selection
    </h1>
</section>

<section class="content filter-form-wrapper">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">                
                <div class="box-body">
                    <table id="user-list" class='table table-bordered table-striped'>
                    </table>
                </div>
                <div class="results-found-count">
                    <span class="filters-count">0</span> Total Results
                </div>
                <div class="row">
                    <div  class="col-sm-4" style="margin: 13px">
                        @can(config('perm.saveNotification'))
                        <button id="save-notification-btn" type="button" status="drafted" class="btn bg-purple save-btn">Save</button>
                        @endcan
                        @can(config('perm.sendNotification'))
                        <button id="send-notification-btn" type="button" status="pending" class="btn bg-purple save-btn">Countinue to send</button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- /.content -->
@endcanany
@can(config('perm.listWaitingNotification'))
<section class="content-header">
    <h1>
        List of notifications waiting for approval
    </h1>
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">                
                <div class="box-body">
                    <table id="notification-waiting" class='table table-bordered table-striped'>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section><!-- /.content -->
@endcan
@can(config('perm.listHistoryNotification'))
<section class="content-header">
    <h1>
        Notification History
    </h1>
</section>

<section class="content">    
    <div class="row">
    
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->            
            <div class="box">                
                <div class="box-body">                
                    <div class="col-sm-12 col-md-2 mb-4" style="float: right;">                        
                        <select id="filter-status" class="form-control select2">                        
                            <option value="">All status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="drafted">Drafted</option>
                        </select>
                    </div>
                    <div style="float: right;">
                        <label >Filter</label>
                    </div>
                    <table id="notification-history" class='table table-bordered table-striped'>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section><!-- /.content -->
@endcan
<form action="#" style="display:none" id="status-form" method="post">
    @csrf
    @method('put')
    <input type="hidden" id="status-input" name="status" value="rejected" />
</form>
@stop

@section('script')
<script src="{{ asset('js/jquery.caret.min.js') }}"></script>
<script src="{{ asset('js/jquery.tag-editor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
<script>
    $(function() {
        $("#country").change(function() {
            getStates($(this).val()); 
        });
     
    
        $("#state").change(function() {
            getCity($(this).val());
        });
    });    
</script>
<script type="text/javascript">

    $(document).ready(function (){
        $("[data-toggle=popover]").popover();
        $('#metatags').tagEditor({
            placeholder: 'Selected Metatags',
            maxTags: 5
        });
        $('#age_groups').tagEditor({
            placeholder: 'Selected age groups (25-50)',
            maxTags:3,
            onChange: function(field, editor, tags) {
                console.log('t: '+tags.length);
            }
        });
        $('#user-list').DataTable( {
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: { 
                url: "{{ route('getUserForSendNotification') }}",
                data: function(data) {
                    data.category_id = $("#category").val();
                    data.sub_category_id = $("#sub_category").val();
                    data.business_id = $("#business_id").val();
                    data.user_id = $("#user_name").val();
                    data.country = $("#country").val();
                    data.state = $("#state").val();
                    data.city = $("#city").val();
                    data.district = $("#district").val();
                    data.gender = $("#gender").val();
                    data.membership_type = $("#verification_level").val();
                    data.education = $("#education_quilification").val();
                    data.caste = $("#caste").val();
                    data.meta_tags = $("#metatags").val();
                    data.age_groups = $("#age_groups").val();
                    data.sender_type = $('input[name="notificationType"]:checked').val();
                    data.notification_to = $('input[name="sendNotification"]:checked').val();
                }},
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id', title: 'User Id'},
                {data: 'business_id', name: 'singlebusiness.id', title: 'Business Id'},
                {data: 'business_name', name: 'singlebusiness.name', title: 'Business Name'},
                {data: 'name', name: 'name', title: 'User Name'},
                {data: 'phone', name: 'country_code', title: 'Mobile Number'},
                {data: 'membership_type', name: 'singlebusiness.membership_type', title: 'Business Status'}
            ],
            drawCallback: function( settings, start, end, max, total, pre ) {  
                //console.log(this.fnSettings()); /* for json response you can use it also*/
                var userCount = this.fnSettings().fnRecordsDisplay();
                $(".filters-count").html(userCount);
                if(userCount > 0) {
                    $("#send-notification-btn-all,#send-notification-btn").prop('disabled',false);
                    $("#save-notification-btn").prop('disabled',false);
                } else {
                    $("#send-notification-btn-all,#send-notification-btn").prop('disabled',true);
                    $("#save-notification-btn").prop('disabled',true);

                }
            }
        });
        @can(config('perm.listWaitingNotification'))
        $('#notification-waiting').DataTable( {
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            searching: false,
            //ordering: false,
            ajax: { 
                url: "{{ route('notification.index') }}",
                data: function(data) {
                    data.status = 'pending';
                    data.notification_for = 'approval';
                }},
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id', title: 'Id'},
                {data: 'title', name: 'title', title: 'Title',orderable:false},
                {data: 'external_link', name: 'external_link', title: 'External Link',orderable:false},
                {data: 'sender_type', name: 'sender_type', title: 'Recipients',orderable:false},
                {data: 'sent_at', name: 'sent_at', title: 'Date',orderable:false},
                {data: 'user.name', name: 'user.name', title: 'Created By',orderable:false},
                {data: 'action', title: 'Action', orderable:false,searchable:false},
            ]
        });
        @endcan
        @can(config('perm.listHistoryNotification'))
        $('#notification-history').DataTable( {
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            searching: false,
            //ordering: false,
            ajax: { 
                url: "{{ route('notification.index') }}",
                data: function(data) {
                    data.status = $("#filter-status").val();
                }
            },
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id', title: 'Id'},
                {data: 'title', name: 'title', title: 'Title',orderable:false},
                {data: 'external_link', name: 'external_link', title: 'External Link',orderable:false},
                {data: 'sender_type', name: 'sender_type', title: 'Recipients',orderable:false},
                {data: 'sent_at', name: 'sent_at', title: 'Last Sent',orderable:false},
                {data: 'user.name', name: 'user.name', title: 'Created By',orderable:false},
                {data: 'status', name: 'status', title: 'Status',orderable:false, class: 'str-cap'},
                {data: 'action', title: 'Action', orderable:false,searchable:false},
            ]
        });
        @endcan
        $('#user-list_filter input').unbind();
        $("#user-list_filter input").on('change',function(e) {
            $('#user-list').DataTable().search($(this).val()).draw();
        });
        $("#filter-status").on('change',function(e) {
            $('#notification-history').DataTable().ajax.reload();
        });
        $("#minAge").on('input',function() {
            $("#showMinAge").html(this.value);
        });
        $("#maxAge").on('input',function() {
            $("#showMaxAge").html(this.value);
        });
        $("#addAgeGroup").on('click',function() {
            var minAge = parseInt($("#minAge").val());
            var maxAge = parseInt($("#maxAge").val());
            if(minAge < maxAge) {
                addAgeGroups(minAge+'-'+maxAge);
            } else {
                alert("Please select maximum age greater than minimum age");
            }
        });
        $("#categoty_tags").on('change',function() {
            addTagsToOption($(this).val());
        });

        $("#filter-user-btn").on('click',function(e) {
            $('#user-list').DataTable().ajax.reload();
        });

        $("#clear-btn").on('click',function() {
            $("#filter-form").trigger("reset");
            removeAllTags('#metatags');
            removeAllTags('#age_groups');            
            $('.select2').trigger('change.select2');
            $('#user-list').DataTable().ajax.reload();
        });
        $('input[name="sendNotification"]').on('click',function() {
            var selectedValue = $('input[name="sendNotification"]:checked').val();
            if(selectedValue == 'sendToAll') {
                showSendToAll();
                $("#clear-btn").trigger('click');
                $('#sendToAllEveryone').prop('checked', true);
            } else {
                showTargetBased();
                showTargetFilters(true);
                $('#targetEveryone').prop('checked', true);
                $('#user-list').DataTable().ajax.reload();
            }
        });

        $('input[name="notificationType"]').on('click',function() {
            var selectedValue = $('input[name="notificationType"]:checked').val();
            if(selectedValue == 'target_member') {
                //$("#user_name").parent().removeClass('mt15');
                $("#clear-btn").trigger('click');
                showTargetFilters();
            } else {
                //$("#user_name").parent().addClass('mt15');
                showTargetFilters(true);
                $('#user-list').DataTable().ajax.reload();
            }
        });

        var topicPushNotificationRules = {
            title: {
                required: true
            },
            description:{
                required: true
            },
            external_link:{
                url: true
            }
        };
        $("#sendPushNotification").validate({
            rules: topicPushNotificationRules
        });

        $("#category").change(function() {
            getSubCategory($(this).val());
            getMetaTags($(this).val());
        });


        $('#categoty_tags,#gender,#verification_level').select2();
        $('#category').select2({
            placeholder: 'Select Category'
        });

        $('#sub_category').select2({
            placeholder: 'Select Sub Category'
        });


        $('#country').select2({
            placeholder: 'Select Country'
        });

        $('#state').select2({
            placeholder: 'Select State'
        });
        $('#city').select2({
            placeholder: 'Select City'
        });
        $('#district').select2({
            placeholder: 'Select District'
        });

        $('#education_quilification').select2({
            placeholder: 'Select Education Qualifications'
        });

        $('#caste').select2({
            placeholder: 'Select Caste/Samaj'
        });

        $('#business_id').select2({
            placeholder: 'Select Business',
            ajax: {
                url: '{{ url("admin/auto-complete-business") }}',
                dataType: 'json',
                delay: 250,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        $('#user_name').select2({
            placeholder: 'Select User',
            ajax: {
                url: '{{ route("autoCompleteUser") }}',
                dataType: 'json',
                delay: 250,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        $("#send-notification-btn,#save-notification-btn,#send-notification-btn-all").click(function() {
            $("#sendPushNotification").validate();
            if($("#sendPushNotification").valid()) {
                $(this).prop('disabled',true);
                var formData = $('#sendPushNotification').serializeArray();
                var filterForm = $('#filter-form').serializeArray();
                var status = $(this).attr('status');
                var sender_type = $('input[name="notificationType"]:checked').val();
                var notification_to = $('input[name="sendNotification"]:checked').val();
                console.log(formData);
                console.log(filterForm);
                console.log(JSON.stringify(filterForm));
                filterForm.push({name: 'sender_type',value: sender_type });
                filterForm.push({name: 'notification_to',value: notification_to});
                formData.push({name: 'filters_data',value: JSON.stringify(filterForm)});
                formData.push({name: 'status',value: status});
                formData.push({name: 'sender_type',value: sender_type });
                $.ajax({
                    type: "POST",
                    url: $("#sendPushNotification").attr('action') ,
                    data: formData,
                    success: function( response ) { 
                        if(response.status) {
                            showSuccessMessage(response.message);
                            resetFiltersAndForm();
                        } else {
                            showErrorMessage(response.message);
                        }  
                        console.log(response);
                    }
                });
            } else {
                console.log("form validation failed");
                window.scrollTo(0 , 0);
            }
        });

        $(document).on("click",".reject-notification",function() {
            var isConfirm = confirm('Are you sure? you want to reject this notification.');
            if(isConfirm) {
                $("#status-form").attr('action',$(this).data('url'));
                $("#status-input").val('rejected');
                $("#status-form").submit();
            }            
        });
        
        $(document).on("click",".approve-notification",function() {
            var isConfirm = confirm('Are you sure? you want to approve this notification.');
            if(isConfirm) {
                $("#status-form").attr('action',$(this).data('url'));
                $("#status-input").val('approved');
                $("#status-form").submit();
            }            
        });

        $(document).on("click",".resend-notification",function() {
            var id = $(this).data('id');
            if(id > 0) {
                $.ajax({
                    url: "{{route('get.notification')}}",
                    method: "POST",
                    data: {notification_id : id},
                    success: function( response ) {
                        if(response.status) {
                            $("#title").val(response.data.title);
                            $("#description").val(response.data.description);
                            $("#external_link").val(response.data.external_link);
                            $("#target_link").val(response.data.target_link);
                            
                            if((response.data.sender_type == 'all' || response.data.sender_type == 'all_member' || response.data.sender_type == 'all_business') && response.data.filters_data.notification_to == 'sendToAll') {
                                $("#sendToAll").trigger('click');
                                $('input[name=notificationType][value="'+response.data.sender_type+'"]').prop('checked', true);
                                showSendToAll();
                            } else {
                                $("#targetSpecficAudience").trigger('click');
                                $('input[name=notificationType][value="'+response.data.sender_type+'"]').prop('checked', true);
                                showTargetBased();
                                if(response.data.sender_type == 'target_member') {
                                    showTargetFilters();
                                } else {
                                    showTargetFilters(true);
                                }
                            }
                            if(typeof response.data.filters_data.business_id != 'undefined' ) {
                                setBusiness(response.data.filters_data.business_id);
                            }
                            if(typeof response.data.filters_data.sub_category_id != 'undefined' ) {
                                getSubCategory(response.data.filters_data.category_id,response.data.filters_data.sub_category_id);
                            }
                            
                            if(typeof response.data.filters_data.user_id != 'undefined' ) {
                                setUser(response.data.filters_data.user_id);
                            }

                            if(typeof response.data.filters_data.category_id != 'undefined' ) {
                                $("#category").val(response.data.filters_data.category_id).trigger('change.select2');
                            }
                            if(typeof response.data.filters_data.gender != 'undefined' ) {
                                $("#gender").val(response.data.filters_data.gender).trigger('change.select2');
                            }
                            $("#verification_level").val(response.data.filters_data.membership_type).trigger('change.select2');
                            if(typeof response.data.filters_data.country != 'undefined' ) {
                                $("#country").val(response.data.filters_data.country).trigger('change.select2');
                            }
                            if(typeof response.data.filters_data.state != 'undefined' ) {
                                $("#state").val(response.data.filters_data.state).trigger('change.select2');
                            }
                            if(typeof response.data.filters_data.city != 'undefined' ) {
                                $("#city").val(response.data.filters_data.city).trigger('change.select2');
                            }
                            if(typeof response.data.filters_data.district != 'undefined' ) {
                                $("#district").val(response.data.filters_data.district).trigger('change.select2');
                            }
                            
                            if(typeof response.data.filters_data.education != 'undefined' ) {
                                $("#education_quilification").val(response.data.filters_data.education).trigger('change.select2');
                            }
                            if(typeof response.data.filters_data.caste != 'undefined' ) {
                                $("#caste").val(response.data.filters_data.caste).trigger('change.select2');
                            }                           
                            if(response.data.filters_data.meta_tags != "") {
                                $('#metatags').tagEditor('addTag', response.data.filters_data.meta_tags);
                            }
                            if(response.data.filters_data.age_groups != "") {
                                $('#age_groups').tagEditor('addTag', response.data.filters_data.age_groups);
                            }
                            setTimeout(function () {
                                $("#filter-user-btn").trigger('click');
                            },4000)
                            window.scrollTo(0 , 0);
                        }
                    }
                });
            }           
        });
    });

    function removeAllTags(input) {
        var tags = $(input).tagEditor('getTags')[0].tags;
        for (i = 0; i < tags.length; i++) { $(input).tagEditor('removeTag', tags[i]); }
    }

    function showSendToAll() {
        $('.send-to-all-section').show();
        $(".filter-form-wrapper").hide();
    }
    function showTargetBased() {
        $('.send-to-all-section').hide();
        $(".filter-form-wrapper").show();
    }

    function showTargetFilters(isBusinessTarget = false) {
        if(isBusinessTarget) {
            $(".filter-form-wrapper .col-md-6").show();
        } else {
            $(".filter-form-wrapper .col-md-6").hide();
            $(".filter-form-wrapper .member-section").show();
        }
    }

    function setBusiness(ids) {
        $('#business_id')
                        .find('option')
                        .remove()
                        .end()
                        .append('<option value="">Select Business</option>');
        if(ids.length > 0) {
            $.ajax({
                url: '{{ url("admin/auto-complete-business") }}',
                method:'POST',
                data: {ids : ids},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function( response ) {
                    console.log(response);
                    $.each(response, function(key, value) {
                        var newOption = new Option(value.text, value.id, false, false);
                        $('#business_id').append(newOption).trigger('change.select2');
                    });

                    $("#business_id").val(ids).trigger('change.select2');
                }
            });
        }
    }

    function setUser(ids) {
        $('#user_name')
                        .find('option')
                        .remove()
                        .end()
                        .append('<option value="">Select User</option>');
        if(ids.length > 0) {
            $.ajax({
                url: '{{ route("autoCompleteUser") }}',
                method:'POST',
                data: {ids : ids},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function( response ) {
                    $.each(response, function(key, value) {
                        var newOption = new Option(value.text, value.id, false, false);
                        $('#user_name').append(newOption).trigger('change.select2');
                    });

                    $("#user_name").val(ids).trigger('change.select2');
                }
            });
        }
    }

    function getStates(countryNames, selected = []) {
        $('#state')
                .find('option')
                .remove()
                .end()
                .append('<option value="">Select State</option>');
        $.ajax({
            type: "POST",
            url: "{{route('stateListByCountryName')}}",
            data: {countryNames: countryNames},
            success: function( response ) {                
                console.log(response);
                $.each(response.data, function(key, value) {
                    var newOption = new Option(value.name, value.name, false, false);
                    $('#state').append(newOption);
                });
                $('#state').trigger('change.select2');
            }
        }); 
    }

    function getCity(states, selected = []) {
        $('#city')
                .find('option')
                .remove()
                .end()
                .append('<option value="">Select City</option>');
        $.ajax({
            type: "POST",
            url: "{{route('cityListByStateName')}}",
            data: {stateNames: states},
            success: function( response ) { 
                $.each(response.data, function(key, value) {
                    var newOption = new Option(value.name, value.name, false, false);
                    $('#city').append(newOption);
                });
            }
        }); 
    }

    function getSubCategory(categoryId, selected = []) {
        $('#sub_category')
                        .find('option')
                        .remove()
                        .end()
                        .append('<option value="">Select Sub Category</option>');
        if(categoryId != null && categoryId.length > 0) {
            $.ajax({
                type: "POST",
                url: "{{route('getSubCategory')}}",
                data: {category_id : categoryId},
                success: function( response ) {
                    $.each(response.data, function(key, value) {   
                        $('#sub_category').append($("<option></option>")
                                            .attr("value",value.id)
                                            .text(value.name)); 
                    });

                    if(selected.length > 0) {
                        $("#sub_category").val(selected).trigger('change.select2');
                    }
                }
            });
        }
    }

    function getMetaTags(categoryId) {
        $('#categoty_tags')
                        .find('option')
                        .remove()
                        .end()
                        .append('<option value="">Select Metatags</option>');
        if(categoryId != null && categoryId.length > 0) {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': "{{csrf_token()}}" },
                type: "POST",
                url: "{{ url('/admin/search/businessmetatags')}}" ,
                data: {categoryId: categoryId},
                success: function( metatags ) {                        
                    $.each(metatags, function(key, value) {   
                        $("#categoty_tags option[value='"+value+"']").remove();  
                        $('#categoty_tags').append($("<option></option>")
                                            .attr("value",value)
                                            .text(value)); 
                    });
                }
            });
        }  
    }

    function addTagsToOption(selectval) {
        $('#metatags').tagEditor('addTag', selectval);
    }

    function addAgeGroups(selectval) {
        console.log(selectval);
        $('#age_groups').tagEditor('addTag', selectval);
    }

    function showSuccessMessage(message) {
        $(".ajex-message").html('<div class="row"><div class="col-md-12"><div class="box-body"><div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button><h4><i class="icon fa fa-check"></i> Success</h4>'+message+'</div></div></div></div>');
    }

    function showErrorMessage(message) {
        $(".ajex-message").html('<div class="row"><div class="col-md-12"><div class="box-body"><div class="alert alert-error alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button><h4><i class="icon fa fa-check"></i> Error</h4>'+message+'</div></div></div></div>');
    }

    function resetFiltersAndForm() {
        $("#sendPushNotification").trigger("reset");
        $("#filter-form").trigger("reset");
        $('.select2').trigger('change.select2');
        $("#send-notification-btn,#send-notification-btn-all,#save-notification-btn").prop('disabled',false);
        $('#user-list,#notification-waiting,#notification-history').DataTable().ajax.reload();
    }
</script>
@stop

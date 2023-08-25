@extends('Admin.Master')

@section('content')

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
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
                <form id="sendPushNotification" class="form-horizontal" method="post" action="{{ url('/admin/sendPushNotification/') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="box-body">

                        <div class="form-group">
                            <label for="title" class="col-sm-2 control-label">{{trans('labels.title')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="title" name="title">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="col-sm-2 control-label">{{ trans('labels.description') }}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <textarea name="description" id="description" cols="113" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.sendbtn')}}</button>
                        </div>
                    </div><!-- /.box-footer -->

                </form>

            </div>
            <!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.notifications')}}
    </h1>
</section>
 
<!-- Main content -->

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">
                <form class="form-horizontal" action="{{ url('/admin/notifications/') }}" > 
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                
                <div class="content">
                    <div class="row"> 
                        <label for="category" class="col-sm-1 control-label">Search</label>
                    
                        <?php
                        // print_r($postData);die();
                            if (old('name'))
                                $Searchname = old('name');
                            elseif (isset($postData['name']))
                                $Searchname = $postData['name'];
                            else
                                $Searchname = '';
                            ?>
                     <div class="col-sm-2">
                        <input type="text" name="name" value="{{$Searchname}}" class="form-control" placeholder="Search Business name" />
                     </div>
                     <div class="col-sm-2">
                        <select name="category" id="category" class="form-control select2">
                            
                            <option value="">Choose category </option>

                          @foreach($categories  as $cat) 

                            <option value="{{$cat->id}}" {{(isset($postData['category']) && $postData['category'] == $cat->id)?'selected':''}}>{{$cat->name}} </option>
                          @endforeach

                        </select>
                     </div>
                     <div class="col-sm-2">
                        <select name="country" id="address_country" class="form-control select2">
                            
                            <option value="">Choose country </option>

                            @foreach($country as $val) 
                            <option value="{{$val->name}}" {{(isset($postData['country']) && $postData['country'] == $val->name)?'selected':''}}>{{$val->name}} </option>
                            @endforeach

                        </select>
                     </div>
                     <div class="col-sm-2">
                        <select name="state" id="address_state" class="form-control select2">
                            
                            <option value="">Choose state </option>

                            @foreach($state as $val2) 
                                <option value="{{$val2->name}}" {{(isset($postData['state']) && $postData['state'] == $val2->name)?'selected':''}}>{{$val2->name}} </option>
                            @endforeach

                        </select>
                     </div>
                     <div class="col-sm-2">
                        <select name="city" id="address_city" class="form-control select2">                        
                            <option value="">Choose city </option>
                            @foreach($city as $val3) 
                            <option value="{{$val3->name}}" {{(isset($postData['city']) && $postData['city'] == $val3->name)?'selected':''}}>{{$val3->name}} </option>
                            @endforeach
                        </select>
                     </div>
                     </div>
                      <div class="row">
                     <div class="pull-right" style="margin: 13px">
                        <a href="{{ url('/admin/branding/') }}" class="btn bg-purple save-btn">Clear</a>
                            <button type="submit" class="btn bg-purple save-btn">Search</button>
                        </div>
                   </div>
                </div>
            </form>

            <form class="form-horizontal" action="{{ url('/admin/notificationsave/') }}" method="post" enctype="multipart/form-data"> 
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                 
                @if($business)
                <div class="content">

                    <div class="row"> 
                        <div class="box-body">
                         <div class="form-group">
                            <div class="col-sm-12">
                             <h2> Create Group For Notification </h2>
                            </div>
                        </div>
                        </div>
                        </div>
                    <div class="row">
                     <div class="col-sm-4">
                        <label for="title" class="control-label"> Group Name<span class="star_red">*</span></label>
                        <input type="text" name="group_title" class="form-control" value="" placeholder="Notification group name" />
                     </div> 
                     <div class="col-sm-4">
                    <label for="title" class="control-label"> Select Business<span class="star_red">*</span></label>
                     <select name="business_id[]" multiple="multiple" class="form-control select2">
                        @foreach($business as $busi)
                     
                        <option value="{{$busi->id}}">{{$busi->name}} </option>  
                        
                        @endforeach

                    </select>
                    </div>
                    <div class="col-sm-2"> <div class="pull-right" style="margin: 13px">
                            
                                <button type="submit" class="btn bg-purple save-btn">Create Group</button>
                            </div>
                       </div>
                   </div>
                 </div>
               
                @endif
            
            </form>
            </div>
        </div>
    </div>
</section>

<section class="content-header">
    <h1>
       Send {{trans('labels.notifications')}}  
    </h1>
</section>
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">
                
                <div class="box-body">
                @if($notificationgroup)
                    <form id="sendPushNotification1" class="form-horizontal" method="post" action="{{ url('/admin/send/notification/') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="box-body">

                        <div class="form-group ">
                            <label for="title" class="col-sm-2 control-label"> Select Group<span class="star_red">*</span></label>
                        
                            <div class="col-sm-8"> <select name="group_id" class="form-control" >
                             @foreach($notificationgroup as  $notification)
                             <option value="{{$notification->id}}">{{$notification->group_title}}</option>

                                @endforeach
                            </select>
                        </div>
                        
                    </div>
                        <div class="form-group ">
                            <label for="title" class="col-sm-2 control-label">{{trans('labels.title')}}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="title" name="title">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="col-sm-2 control-label">{{ trans('labels.description') }}<span class="star_red">*</span></label>
                            <div class="col-sm-8">
                                <textarea name="description" id="description" cols="113" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn bg-purple save-btn">{{trans('labels.sendbtn')}} {{trans('labels.notifications')}}  </button>
                        </div>
                    </div>
                    <!-- /.box-footer -->

                </form>
                @endif
 
                </div>
            </div>
        </div>
    </div>
</section><!-- /.content -->

<section class="content-header">
    <h1>
        {{trans('labels.notifications')}} Lists
    </h1>
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">
                
                <div class="box-body">
                    <table id="trendingCategoriesListing" class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>Notification</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($notificationgroup)
                            @foreach($notificationgroup as  $notification)
                            <tr>
                                <td>{{$notification->group_title}}</td>
                                <td>
                                    <a href="{{ url('/admin/notificationdelete/').'/'.$notification->id }}" class="btn bg-green ">
                                          Delete
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                             
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section><!-- /.content -->
@stop

@section('script')
<script>


    $(function() {
    $("#address_country").change(function() {
            var selected_country =  $('option:selected', this).val();
            //alert(selected_country);
        var token = '<?php echo csrf_token() ?>';
        $.ajax({
            headers: { 'X-CSRF-TOKEN': token },
            type: "POST",
            url: "{{url('/admin/getState')}}",
            data: {selected_country: selected_country},
            success: function( data ) {
                
                $('#address_state').html(data);
                //alert(data);

              
            }
        });     
            
        });
     
    
     $("#address_state").change(function() {
            var selected_state =  $('option:selected', this).val();
             //alert(selected_state);
        var token = '<?php echo csrf_token() ?>';
        $.ajax({
            headers: { 'X-CSRF-TOKEN': token },
            type: "POST",
            url: "{{url('/admin/getCity')}}",
            data: {selected_state: selected_state},
            success: function( data ) {
                
                $('#address_city').html(data);
                //alert(data);

              
            }
        });     
            
        });
    });

    $('#filetype').change(function(){

        if($('#filetype').val() == 1)
        {
            $('#image-part').show();
            $('#video-part').hide();
            $('#text-part').hide();
        }
        else if($('#filetype').val() == 2)
        {
            $('#video-part').show();
            $('#image-part').hide();
            $('#text-part').hide();
        }
        else if($('#filetype').val() == 3)
        {
            $('#text-part').show();
            $('#video-part').hide();
            $('#image-part').hide();
        }
    });
    $("#addbrandingManagement").validate({
        ignore: "",
        rules: {
            type: {
                required: true,
            }
        },
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
</script>
<script type="text/javascript">

    $(document).ready(function ()
    {
        var topicPushNotificationRules = {
            title: {
                required: true
            },
            description:{
                 required: true
            },
        };
        $("#sendPushNotification").validate({
            ignore: "",
            rules: topicPushNotificationRules,
            messages:
            {
                title: {
                    required: "<?php echo trans('labels.titlerequired')?>"
                },
                description: {
                    required: "<?php echo trans('labels.descriptionrequired')?>"
                }
            }
        });
    });
</script>
@stop

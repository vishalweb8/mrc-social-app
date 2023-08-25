@extends('Admin.Master')
@section('header')
    <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
@stop

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.entities')}}
        <div class="pull-right">
            @can(config('perm.addEntity'))
            <a href="{{ route('entity.create') }}" class="btn bg-purple">
                <i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn')}} {{trans('labels.entity')}}
            </a>
            @endcan
        </div>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                @if(Auth::user()->agent_approved == 0)
                <div class="box-header">
                    <form id="formSearch" class="form-horizontal" method="post" action="{{ url('/admin/allentity') }}">
                        <div class="col-md-2">
                            <?php $countryCodes = Helpers::getCountries(); ?>
                            <select name="country_code" data="" class="form-control select2" id="country_code">
                                <option value="">Country Code</option>
                                @forelse($countryCodes as $codes)
                                    <option value="{{$codes->country_code}}" {{(isset($postData['country_code']) && $postData['country_code'] == $codes->country_code)?'selected':''}}>{{$codes->name}} {{$codes->country_code}} </option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" value="{{(isset($postData['searchText'])) ? $postData['searchText'] : ''}}" name="searchText" class="form-control" placeholder="search text">
                        </div>
                        <div class="col-md-2">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="search" value="1">
                            <select class="form-control" name="fieldtype">
                                <option value=''>{{trans('labels.selectfieldname')}}</option>
                                <option value='category_id' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'category_id')?'selected':''}}>{{trans('labels.category')}}</option>
                                <option value='mobile' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'mobile')?'selected':''}}>{{trans('labels.mobile')}}</option>
                                <option value='users' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'users')?'selected':''}}>{{trans('labels.users')}}</option>
                                <option value='owners' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'owners')?'selected':''}}>{{trans('labels.owners')}}</option>
                                <option value='city' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'city')?'selected':''}}>{{trans('labels.city')}}</option>
                                <option value='state' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'state')?'selected':''}}>{{trans('labels.state')}}</option>
                                <option value='country' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'country')?'selected':''}}>{{trans('labels.country')}}</option>
                                <option value='address' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'address')?'selected':''}}>{{trans('labels.address')}}</option>
                                <option value='latitude' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'latitude')?'selected':''}}>{{trans('labels.latitude')}}</option>
                                <option value='longitude' {{(isset($postData['fieldtype']) && $postData['fieldtype'] == 'longitude')?'selected':''}}>{{trans('labels.longitude')}}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="fieldcheck">
                                <option value=''>{{trans('labels.selectfieldtype')}}</option>
                                <option value='0' {{(isset($postData['fieldcheck']) && $postData['fieldcheck'] == '0')?'selected':''}}>{{trans('labels.null')}}</option>
                                <option value='1' {{(isset($postData['fieldcheck']) && $postData['fieldcheck'] == '1')?'selected':''}}>{{trans('labels.notnull')}}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" name="approved">
                                <option value='' {{(isset($postData['approved']) && $postData['approved'] == '')?'selected':''}}>{{trans('labels.all')}}</option>
                                <option value='0' {{(isset($postData['approved']) && $postData['approved'] == '0')?'selected':''}}>{{trans('labels.pending')}}</option>
                                <option value='1' {{(isset($postData['approved']) && $postData['approved'] == '1')?'selected':''}}>{{trans('labels.approved')}}</option>
                                <option value='2' {{(isset($postData['approved']) && $postData['approved'] == '2')?'selected':''}}>{{trans('labels.rejected')}}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" class="btn bg-purple" name="searchBtn" id="searchBtn" value="{{trans('labels.search')}}"/>
                            <a href="{{ url('/admin/allentity') }}">
                                <input type="button" class="btn bg-purple" name="clearBtn" id="clearBtn" value="{{trans('labels.clear')}}"/>
                            </a>
                        </div>
                    </form>
                </div>
                @endif
                @if(Auth::user()->agent_approved == 1)
                    <div class="box-header">
                        <form id="formSearch" class="form-horizontal" method="post" action="{{ url('/admin/allentity') }}">
                            <div class="col-md-4">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="search" value="1">
                                <input type="text" value="{{(isset($postData['searchText'])) ? $postData['searchText'] : ''}}" name="searchText" class="form-control" placeholder="search text">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="type">
                                    <option value=''>{{trans('labels.selecttype')}}</option>
                                    <option value='created_by' {{(isset($postData['type']) && $postData['type'] == 'created_by')?'selected':''}}>{{trans('labels.createdby')}}</option>
                                    <option value='assign_to' {{(isset($postData['type']) && $postData['type'] == 'assign_to')?'selected':''}}>{{trans('labels.assignto')}}</option>
                                    <option value='all' {{(isset($postData['type']) && $postData['type'] == 'all')?'selected':''}}>{{trans('labels.all')}}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="submit" class="btn bg-purple" name="searchBtn" id="searchBtn" value="{{trans('labels.search')}}"/>
                                <a href="{{ url('/admin/allentity') }}">
                                    <input type="button" class="btn bg-purple" name="clearBtn" id="clearBtn" value="{{trans('labels.clear')}}"/>
                                </a>
                            </div>
                        </form>
                    </div>
                @endif
                <div class="box-body table-responsive">
                    <table class="table table-hover" id="business">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.name')}}</th>
                                <th>{{trans('labels.type')}}</th>
                                <th>{{trans('labels.user')}}</th>
                                <th>{{trans('labels.category')}}</th>
                                <th>{{trans('labels.mobile')}}</th>
                                <th>{{trans('labels.approvalstatus')}}</th>
                                @if(Auth::user()->agent_approved == 1)
                                    <th>{{trans('labels.status')}}</th>
                                @endif
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($businessList as $key=>$value)
                            <tr>
                                <td>
                                    {{$value->id}}
                                </td>
                                <td>
                                    {{$value->name}}
                                </td>
                                <td>
                                    {{$value->entity_type}}
                                </td>
                                <td>
                                    @if(isset($value->user_id) && $value->user_id != '')
                                        <a href="{{ url('/admin/edituser') }}/{{Crypt::encrypt($value->user_id)}}" target="_blank">
                                            {{$value->user_full_name}}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    {{$value->categories}}
                                </td>
                                <td>
                                    @if($value->country_code)
                                        ({{$value->country_code}}){{$value->mobile}} 
                                    @else
                                        {{$value->mobile}} 
                                    @endif
                                    
                                </td>
                                <td>
                                    @if($value->approved == 0)
                                        @can(config('perm.approveEntity'))
                                        <div class="business_approve">
                                            <span class="label label-info" onclick="approved(this,{{$value->id}})" style="cursor: pointer;">
                                                {{trans('labels.pending')}}
                                            </span>
                                        </div>
                                        @endcan
                                    @elseif($value->approved == 1)
                                        <span class="label label-success">{{trans('labels.approved')}}</span>
                                    @else
                                        <span class="label label-danger">{{trans('labels.rejected')}}</span>
                                    @endif
                                </td>
                                @if(Auth::user()->agent_approved == 1)
                                    <td>
                                        @if($value->created_by == Auth::id())
                                            <span class="label label-success">Created By</span>
                                        @elseif($value->created_by != Auth::id())
                                            <span class="label label-success">Assign to</span>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    @can(config('perm.viewEntity'))
                                    <a href="{{ route('entity.show',Crypt::encrypt($value->id)) }}">
                                        <span data-toggle="tooltip" data-original-title="View" class='glyphicon glyphicon-eye-open'></span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.editEntity'))
                                    <a href="{{ url('admin/user/business/edit') }}/{{Crypt::encrypt($value->id)}}">
                                        <span data-toggle="tooltip" data-original-title="Edit" class='glyphicon glyphicon-edit'></span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.deleteEntity'))
                                    @if(Auth::user()->agent_approved == 0)
                                        <a onclick="return confirm('Are you sure you want to delete ?')" href="{{ url('/admin/user/business/delete') }}/{{Crypt::encrypt($value->id)}}">
                                            <span data-toggle="tooltip" data-original-title="Delete" class='glyphicon glyphicon-remove'></span>
                                        </a>&nbsp;&nbsp;
                                    @endif
                                    @endcan
                                    @can(config('perm.manageService'))
                                    <a href="{{ url('/admin/user/business/service') }}/{{Crypt::encrypt($value->id)}}">
                                        <span  class="badge bg-light-blue" data-toggle="tooltip" data-original-title="Manage Service" style="margin-bottom: 3px;">S</span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.manageProduct'))
                                    <a href="{{ url('/admin/user/business/product') }}/{{Crypt::encrypt($value->id)}}">
                                        <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Manage Product" style="margin-bottom: 3px;">P</span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.manageOwner'))
                                    <a href="{{ url('/admin/user/business/owner') }}/{{Crypt::encrypt($value->id)}}">
                                        <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Manage Owner" style="margin-bottom: 3px;">O</span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.manageMembership'))
                                    <a href="{{ url('/admin/user/business/membership') }}/{{Crypt::encrypt($value->id)}}">
                                        <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Manage Membership" style="margin-bottom: 3px;">M</span>
                                    </a>
                                    @endcan
                                    @can(config('perm.manageWebsite'))
                                    <a href="{{ url('admin/allpublicwebsite/add') }}/{{Crypt::encrypt($value->id)}}">
                                        <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Public Website" style="margin-bottom: 3px;">W</span>
                                    </a>
                                    @endcan
                                    @can(config('perm.sendWhatsapp'))
                                    @if(config('app.name') != 'RYEC')
                                      <?php   
                                      if($value->rating){

                                        $rate = round($value->rating,1);
                                      }else{

                                        $rate = 0;
                                      }

                                    $aaaa = ("{$value->name} \r\n");
                                    $aaaa .=  ("Owner: {$value->user_full_name} \r\n");
                                    $aaaa .=  ("Category: {$value->categoryName}  \r\n");
                                    $aaaa .=  ("Ratings: {$rate}/5 \r\n");
                                    $aaaa .=  ("Mobile: ({$value->country_code}){$value->mobile}\r\n");
                                    $aaaa .=  ("Email: {$value->email} \r\n");
                                    $aaaa .=  ("Website: {$value->website_url} \r\n");
                                    $aaaa .=  ("Address: {$value->address}\r\n");

                                    $aaaa .=  ("- Shared using MyRajasthan (My Rajasthan Club) www.myrajasthanclub.com\r\n");
                                    $aaaa .=  ("Android App: bit.ly/myrajasthan_android\r\n");
                                    $aaaa .=  ("iOS App: bit.ly/myrajasthan_ios.\r\n");
                                    $aaaa .=  $whatsappMessage;
                                    $aaaa = urlencode( $aaaa );
                                    ?>
                                    @if($value->country_code)

                                     <a href="https://api.whatsapp.com/send?phone=({{$value->country_code}}){{$value->mobile}}&text={{$aaaa}}" target="_blank">
                                        <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Send Whatsapp" style="margin-bottom: 3px;">Send Whatsapp</span>
                                    </a>
                                        
                                    @else

                                     <a href="https://api.whatsapp.com/send?phone={{$value->mobile}}&text={{$aaaa}}" target="_blank">
                                        <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Send Whatsapp" style="margin-bottom: 3px;">Send Whatsapp</span>
                                    </a>
                                         
                                    @endif
                                    @endif
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>

                    @if(Auth::user()->agent_approved == 1)
                        <div class="pull-right">
                            <?php echo $businessList->render(); ?>
                        </div>
                    @else
                        @if (isset($businessList) && !empty($businessList))
                            @php 
                                $filterList = [];
                            @endphp   
                            @if(isset($postData['searchText']) && $postData['searchText'] != '')
                                <?php
                                    $searchtext = $postData['searchText'];
                                    $filterList['searchText'] = $postData['searchText'];
                                ?>
                            @else
                                <?php 
                                    $searchtext = '';
                                    $filterList['searchText'] = '';
                                ?>
                            @endif
                            @if(isset($postData['fieldtype']) && $postData['fieldtype'] != '')
                                <?php
                                    $fieldtype = $postData['fieldtype'];
                                    $filterList['fieldtype'] = $postData['fieldtype'];
                                ?>
                            @else
                                <?php 
                                  $fieldtype = '';
                                  $filterList['fieldtype'] = ''; 
                                ?>
                            @endif
                            @if(isset($postData['fieldname']) && $postData['fieldname'] != '')
                                <?php
                                    $fieldname = $postData['fieldname'];
                                    $filterList['fieldname'] = $postData['fieldname'];
                                ?>
                            @else
                                <?php 
                                    $fieldname = ''; 
                                    $filterList['fieldname'] = '';
                                ?>
                            @endif
                            @if(isset($postData['fieldcheck']) && $postData['fieldcheck'] != '')
                                <?php
                                    $fieldcheck = $postData['fieldcheck'];
                                    $filterList['fieldcheck'] = $postData['fieldcheck'];
                                ?>
                            @else
                                <?php 
                                    $fieldcheck = ''; 
                                    $filterList['fieldcheck'] = '';
                                ?>
                            @endif
                            @if(isset($postData['approved']) && $postData['approved'] != '')
                                <?php
                                    $approved = $postData['approved'];
                                    $filterList['approved'] = $postData['approved'];
                                ?>
                            @else
                                <?php 
                                    $approved = '';
                                    $filterList['approved'] = '';  
                                ?>
                            @endif
                            <?php $country_code = ''; ?>
                            @if(isset($postData['country_code']) && $postData['country_code'] != '')
                                <?php
                                    $filterList['country_code'] = $postData['country_code'];
                                ?>
                            @endif
                            <div class="pull-right">                                
                                @if(isset($categoryId) && $categoryId != '')
                                @else
                                    <?php echo $businessList->appends($filterList)->render(); ?>
                                    <?php //echo $businessList->appends(['searchText' => $searchtext, 'fieldtype' => $fieldtype, 'fieldcheck' => $fieldcheck, 'approved' => $approved])->render(); ?>
                                @endif
                            </div>
                        @endif
                    @endif
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
            
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
<script type="text/javascript">
    // <?php if(Auth::user()->agent_approved == 0) { ?>
    // $(document).ready(function() {
    //     $('#business').DataTable({
    //        "aaSorting": []
    //     });
    // });
    // <?php } ?>

    function approved(eventBtn,businessId)
    {
        var token = '<?php echo csrf_token() ?>';
        $.ajax({
            headers: { 'X-CSRF-TOKEN': token },
            type: "POST",
            url: "{{url('/admin/user/business/approved')}}",
            data: {businessId: businessId},
            success: function( data ) {
                $(eventBtn).closest('.business_approve').html('<span class="label label-success">Approved</span>');
            }
        });
    }
    
    $(function(){
        $('.select2').select2();
    })
</script>
@stop

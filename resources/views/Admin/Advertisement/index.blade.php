@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Market Place : Advertisements</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success alert-dismissable" id="alert_dashboard" style="display:none;">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                <h4><i class="icon fa fa-check"></i> Success!</h4>
                Advertisement has been approved successfully.
            </div>
            <div class="alert alert-success alert-dismissable" id="red_alert_dashboard" style="display:none;">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                <h4><i class="icon fa fa-check"></i> Rejected!</h4>
                Advertisement has been rejected successfully.
            </div>
            <div class="box">
                <div class="box-header">
                    <form id="formSearch" class="form-horizontal" method="post" action="{{ url('/admin/advertisement') }}">
                        <div class="col-md-3">
                            <input type="text" value="{{(isset($postData['searchText'])) ? $postData['searchText'] : ''}}" name="searchText" class="form-control" placeholder="search text">
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="search" value="1">
                            <select class="form-control" name="fieldtype">
                                <option value=''>{{trans('labels.selectfieldname')}}</option>                                
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
                            <a href="{{ url('/admin/advertisement') }}">
                                <input type="button" class="btn bg-purple" name="clearBtn" id="clearBtn" value="{{trans('labels.clear')}}"/>
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="box-body table-responsive">
                    <div>
                        <div>
                            @can(config('perm.approveMarketplaceAds'))                         
                                <button id="btnApproved" class="btn bg-green" data-toggle="tooltip" data-original-title="{{trans('labels.approved')}}"><i class="fa fa-check"></i> {{trans('labels.approved')}}</button>
                            @endcan
                            @can(config('perm.rejectMarketplaceAds'))
                                <button id="btnRejected" class="btn bg-danger" data-toggle="tooltip" data-original-title="{{trans('labels.rejected')}}"><i class="fa fa-close"></i> {{trans('labels.rejected')}}</button>
                            @endcan
                            Approve or reject the advertisement: 
                        </div>
                    </div>
                    <table class="table table-hover" id="tblAdvertisement">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="chkSelectAll" id="chkSelectAll"  /></th>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.name')}}</th>
                                <th>{{trans('labels.user')}}</th>
                                <th>{{trans('labels.category')}}</th>
                                <th>{{trans('labels.approvalstatus')}}</th>
                                <th>{{trans('labels.advertisement_status_open_closed')}}</th>
                                @if(Auth::user()->agent_approved == 1)
                                    <th>{{trans('labels.status')}}</th>
                                @endif
                                <th>{{trans('labels.createdat')}}</th>
                                <th>{{trans('labels.deleted')}}</th>
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($advertisementList as $key=>$value)
                            <tr>
                                <td><input type="checkbox" name="chkApproveReject-{{$value->id}}" id="chkApproveReject-{{$value->id}}" value="{{$value->id}}"  /></td>
                                <td>{{$value->id}}</td>
                                <td><a href="{{ url('admin/advertisement/edit') }}/{{Crypt::encrypt($value->id)}}">{{$value->name}}</a></td>
                                <td>
                                    @if(isset($value->user_id) && $value->user_id != '')
                                        <a href="{{ url('/admin/edituser') }}/{{Crypt::encrypt($value->user_id)}}" target="_blank">
                                            {{$value->user_full_name}}
                                        </a>
                                    @endif
                                </td>
                                <td>{{$value->categories}}</td>
                                <td>
                                    @if($value->approved == 0)
                                        @can(config('perm.approveMarketplaceAds'))
                                        <div class="business_approve">
                                            <span class="label label-info" onclick="fnApproved({{$value->id}})" style="cursor: pointer;">
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
                                <td>
                                    @if($value->is_closed == 0)
                                        <span class="label label-success">{{trans('labels.advertisement_status_open')}}</span>                                                                
                                    @else
                                        <span class="label label-danger">{{trans('labels.advertisement_status_closed')}}</span>
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
                                <td><span class="">{{ $value->created_at }}</span></td>
                                <td>
                                    @if($value->deleted_at != '' && auth()->user()->can(config('perm.restoreMarketplaceAds')))
                                    <a onclick="return confirm('Are you sure you want to restore ?')" href="{{ url('/admin/advertisement/restore/') }}/{{Crypt::encrypt($value->id)}}">
                                        <span  data-toggle="tooltip" data-original-title="Restore" class="label label-danger">Deleted</span>
                                    </a>
                                    @endif
                                </td>
                                <td>
                                    @can(config('perm.editMarketplaceAds'))
                                    <a href="{{ url('admin/advertisement/edit') }}/{{Crypt::encrypt($value->id)}}">
                                        <span data-toggle="tooltip" data-original-title="Edit" class='glyphicon glyphicon-edit'></span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.deleteMarketplaceAds'))
                                    @if(Auth::user()->agent_approved == 0)
                                        <a onclick="return confirm('Are you sure you want to delete ?')" href="{{ url('/admin/advertisement/remove/') }}/{{Crypt::encrypt($value->id)}}">
                                            <span data-toggle="tooltip" data-original-title="Delete" class='glyphicon glyphicon-remove'></span>
                                        </a>&nbsp;&nbsp;
                                    @endif
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                    
                    @if (isset($advertisementList) && !empty($advertisementList))
                        @if(isset($postData['searchText']) && $postData['searchText'] != '')
                            <?php
                                $searchtext = $postData['searchText'];
                            ?>
                        @else
                            <?php $searchtext = ''; ?>
                        @endif
                        @if(isset($postData['fieldtype']) && $postData['fieldtype'] != '')
                            <?php
                                $fieldtype = $postData['fieldtype'];
                            ?>
                        @else
                            <?php $fieldtype = ''; ?>
                        @endif
                        @if(isset($postData['fieldname']) && $postData['fieldname'] != '')
                            <?php
                                $fieldname = $postData['fieldname'];
                            ?>
                        @else
                            <?php $fieldname = ''; ?>
                        @endif
                        @if(isset($postData['fieldcheck']) && $postData['fieldcheck'] != '')
                            <?php
                                $fieldcheck = $postData['fieldcheck'];
                            ?>
                        @else
                            <?php $fieldcheck = ''; ?>
                        @endif
                        @if(isset($postData['approved']) && $postData['approved'] != '')
                            <?php
                                $approved = $postData['approved'];
                            ?>
                        @else
                            <?php $approved = ''; ?>
                        @endif
                        <div class="pull-right">
                            <?php echo $advertisementList->appends(['searchText' => $searchtext, 'fieldtype' => $fieldtype, 'fieldcheck' => $fieldcheck, 'approved' => $approved])->render(); ?>
                        </div>
                    @endif
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
            
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $("#chkSelectAll").click(function () {
            $('#tblAdvertisement tbody input[type="checkbox"]').prop('checked', this.checked);
        });

        $("#btnApproved").click(function (event) {
            event.preventDefault();
            advertisementIds = Array();
            $('#tblAdvertisement tbody input[type="checkbox"]:checked').each(function(){
                advertisementIds.push($(this).val());
            });
            fnApproveAdvertisement(advertisementIds);
        });

        $("#btnRejected").click(function (event) {            
            event.preventDefault();
            advertisementIds = Array();
            $('#tblAdvertisement tbody input[type="checkbox"]:checked').each(function(){
                advertisementIds.push($(this).val());
            });
            fnRejectAdvertisement(advertisementIds);
        });
        
        fnReloadPage = function() {
            location.reload();
        }

        fnApproved = function(advertisementId) {
            advertisementIds = Array();
            advertisementIds.push(advertisementId);
            fnApproveAdvertisement(advertisementIds);
        }

        fnApproveAdvertisement = function(advertisementIds) {
            var token = '<?php echo csrf_token() ?>';
            if(advertisementIds.length > 0) {
                tmpAdvertisementId = advertisementIds.join(",");
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': token },
                    type: "POST",
                    url: "{{ url('/admin/advertisement/approved') }}",
                    data: { "advertisementIds" : tmpAdvertisementId },
                    success: function( data ) {
                        $('#alert_dashboard').show();
                        setTimeout(fnReloadPage, 700);   
                    }, 
                    error: function(jqXHR, exception) {
                        alert(jqXHR.status + " " + exception);
                    }
                });
            } else {
                alert("Please select advertisement");
            }
        }

        fnRejectAdvertisement = function(advertisementIds) {
            var token = '<?php echo csrf_token() ?>';
            if(advertisementIds.length > 0) {
                tmpAdvertisementId = advertisementIds.join(",");
                $.ajax({
                    type: "POST",
                    url: "{{ url('/admin/advertisement/rejected') }}",
                    data: { "advertisementIds" : tmpAdvertisementId },
                    processData: true,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', token);
                    },
                    success: function( data ) {
                        $('#red_alert_dashboard').show();
                        setTimeout(fnReloadPage, 700);
                    }, 
                    error: function(jqXHR, exception) {
                        alert(jqXHR.status + " " + exception);
                    }
                });
            } else {
                alert("Please select advertisement");
            }
        }
    });
</script>
@stop
@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{trans('labels.agentrequest')}}</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div class="form-group">
                        <a href="{{ route('listRejecteRequest') }}" class="btn btn-danger">Show Rejected</a> <a href="{{ url('admin/agents') }}" class="btn btn-success">Show Pending</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="agentRequest">
                            <thead>
                                <tr>                                
                                    <th>{{trans('labels.photo')}}</th>
                                    <th style="width: 20%">{{trans('labels.name')}}</th>
                                    <th style="width: 20%">{{trans('labels.email')}} / {{trans('labels.phone')}}</th>
                                    <th style="width: 20%">{{trans('labels.comment')}}</th>
									<th>{{trans('labels.date')}}</th>
                                    @if(!isset($isRejected)) 
                                    <th style="width: 20%">{{trans('labels.action')}}</th>
                                    @else
                                    <th style="width: 20%">Admin Comment</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agentRequestList as $key=>$value)
                                <tr>                                
                                    <td>
                                        @if(isset($value->user) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$value->user->profile_pic) && ($value->user->profile_pic !='')) 
                                            <img style="cursor: pointer;" data-toggle='modal' data-target='#{{$value->id.substr(trim($value->user->profile_pic), 0, -10)}}' src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$value->user->profile_pic) }}" height="40" width="40" title="{{$value->user->profile_pic}}" class="img-circle" />
                                            <div class='modal modal-centered fade image_modal' id='{{$value->id.substr(trim($value->user->profile_pic), 0, -10)}}' role='dialog' style='vertical-align: center;'>
                                                <div class='modal-dialog modal-dialog-centered'>
                                                    <div class='modal-content' style="background-color:transparent;">
                                                        <div class='modal-body'>
                                                        <center>
                                                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                                            <img src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_ORIGINAL_IMAGE_PATH').$value->user->profile_pic) }}" style='width:100%; border-radius:5px;' title="{{$value->user->profile_pic}}" />
                                                        <center>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{$value->user->name}}</td>
                                    <td>
                                        @if($value->user->email && $value->user->phone)
                                            {{$value->user->email}} <br /> {{$value->user->phone}}
                                        @elseif($value->user->email && !$value->user->phone)
                                            {{$value->user->email}}
                                        @elseif(!$value->user->email && $value->user->phone)
                                            {{$value->user->phone}}
                                        @endif
                                    </td>
                                    <td>
                                        {{\Illuminate\Support\Str::limit($value->comment,60)}}
                                        @if(strlen($value->comment) > 60)
                                            <span data-toggle='modal' data-target='#{{$value->id}}' style="cursor: pointer;color:#605ca8;font-weight: bold;">Read more</span>
                                            <div class='modal modal-centered fade image_modal' id='{{$value->id}}' role='dialog' style='vertical-align: center;'>
                                                <div class='modal-dialog modal-dialog-centered'>
                                                    <div class='modal-content' style="background-color:#fff;">
                                                        <div class='modal-body'>
                                                        <center>
                                                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                                            {{$value->comment}}
                                                        <center>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
									<td>{{$value->created_at->format("Y-m-d H:i")}}</td>
                                    @if(!isset($isRejected)) 
                                    <td>
                                        @if($value->user->agent_approved == 0)
                                        <div class="form-action">
                                            @can(config('perm.approveRepresentativeRequests')) 
                                            <a onclick="return confirm('Are you sure you want to approve ?')" href="{{ url('admin/agentrequest') }}/{{Crypt::encrypt($value->id)}}">
                                                <div class="business_approve">
                                                    <button class="btn btn-sm btn-success pull-left" onclick="approved(20)">
                                                        <i class="fa fa-check"></i>&nbsp;Approve
                                                    </button>
                                                </div>
                                            </a>
                                            @endcan
                                            @can(config('perm.rejectRepresentativeRequests'))
                                            <button style="margin-top: 5px;" class="btn btn-sm btn-danger btnRejectMe pull-right" data-id="{{Crypt::encrypt($value->id)}}" >
                                                <i class="fa fa-remove"></i>&nbsp;Reject
                                            </button>
                                            @endcan
                                        </div>
                                        @endif
                                    </td>
                                    @else
                                    <td>{{ $value->admin_comment}}</td>
                                    @endif
                                </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>       
                    </div>             
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
<div class='modal modal-centered fade' id='rejectModal' role='dialog'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
            <form id="frmRejectRequest" method="POST" action="{{ route('rejectAgentRequest') }}">
                <div class='modal-header'>
                    <h3>Reject agent request <button type='button' class='close pull-right' data-dismiss='modal'>&times;</button></h3>                    
                </div>
                <div class='modal-body'>
                    <div class="row">
                        <div class="col-md-12">
                            {{ csrf_field() }}
                            <label class="txtComment">Enter your comment for rejecting agent request:</label>
                            <textarea name="txtComment" rows="5" class="form-control"></textarea>
                            <input type="hidden" name="txtCommentId" id="txtCommentId" />
                        </div>
                    </div>
                    <div class="row"><div class="col-md-12"><span id="helpBlock" class="help-block">Max. 1000 chars.</span></div></div>
                </div>
                <div class='modal-footer'>
                    <button type='submit' id="btnReject" name="btnReject" class="btn btn-success"> Update</button>
                    <button type='button' class='btn btn-danger' data-dismiss='modal'>Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('script')
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.0/additional-methods.js"></script>
<script type="text/javascript">
    
    $(document).ready(function() {
        $('#agentRequest').DataTable({
            hideEmptyCols: true,
            "aaSorting": []
        });

        $('body').on('click', '.btnRejectMe',function() {
            $("body #rejectModal #txtCommentId").val($(this).attr("data-id"));
            $('#rejectModal').modal('show');
        });

        $("#frmRejectRequest").validate({
            ignore: "",
            rules: {
                txtComment: { required: true, },
            }
        });
    }); 
</script>
@stop

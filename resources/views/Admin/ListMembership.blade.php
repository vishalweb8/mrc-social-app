@extends('Admin.Master')
@section('header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />

@stop
@section('content')
<style>
     .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>

        {{trans('labels.memberships')}}
        @if(Auth::user()->agent_approved == 0)
        <div class="pull-right">
            @can(config('perm.addMembership'))
            <a href="{{ url('admin/user/business/membership/add') }}/{{Crypt::encrypt($businessId)}}" class="btn bg-purple"><i class="fa fa-plus"></i>&nbsp;{{trans('labels.addbtn').' '.trans('labels.membership')}}</a>
            @endcan
        </div>
        @endif
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <ol class="breadcrumb">
                        <li><a href="{{url('admin/users')}}"><i class="fa fa-users"></i> Users </a></li>
                        <li><a href="{{url('admin/user/business')}}/{{Crypt::encrypt($businessDetails->user->id)}}">{{$businessDetails->user->name}}</a></li>
                        <li>{{$businessDetails->name}} {{trans('labels.business')}} - {{trans('labels.memberships')}}</li>
                    </ol>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" id="membership">
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.plantype')}}</th>
                                <th>{{trans('labels.startdate')}}</th>
                                <th>{{trans('labels.enddate')}}</th>
                                <th>Status</th>
                                <th>{{trans('labels.headeraction')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($businessDetails->businessMembershipPlans as $key=>$value)
                            <tr>
                                <td>
                                    {{$value->id}}
                                </td>
                                <td>
                                    {{$value->subscriptionPlan->name}}    
                                </td>
                                <td>
                                    {{$value->start_date}}       
                                </td>
                                <td>
                                    {{$value->end_date}}    
                                </td>
                                <td>
                                    @can(config('perm.updateStatusMembership'))
                                    <label class="switch">
                                        <input type="checkbox" name="status" class="status" {{ $value->status == 1 ? 'checked': ''  }} data-id="{{$value->id}}" autocomplete="off" value="{{ $value->status }}">
                                        <span class="slider round"></span>
                                    </label>
                                    @endcan
                                </td>
                         
                                </td>
                                <td>
                                    @can(config('perm.editMembership'))
                                    <a href="{{ url('admin/user/business/membership/edit') }}/{{Crypt::encrypt($value->id)}}">
                                        <span data-toggle="tooltip" {{(Auth::user()->agent_approved == 0) ? 'data-original-title=Edit' : 'data-original-title=View'}} class='glyphicon glyphicon-edit'></span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.deleteMembership'))
                                    @if(Auth::user()->agent_approved == 0)
                                    <a onclick="return confirm('Are you sure you want to delete ?')" href="{{ url('/admin/user/business/membership/delete') }}/{{Crypt::encrypt($value->id)}}">
                                        <span data-toggle="tooltip" data-original-title="Delete" class='glyphicon glyphicon-remove'></span>
                                    </a>
                                    @endif
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- /.box -->
            
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@stop
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#product').DataTable({
            hideEmptyCols: true,
           "aaSorting": []
        });
    });

     $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});   

    $('.status').on('change', function (e) {
       toastr.options = {
          "closeButton": true,
          "newestOnTop": true,
          "positionClass": "toast-top-right"
        };
    var status = $(this).prop('checked') === true ? 1 : 0; ;  
    var id =  $(this).data('id');
    var checked = false;
    var statusChange;
    // console.log(id)
    // console.log(busnessId)
    $.ajax({
               type:'POST',
               url:"{{route('membership.status')}}",
               data: {id:id, status:status},
               success:function(data) {
                // console.log(data)
                  toastr.success(data.message);
               },
               error:function(res) {
                // console.log(res)
                  toastr.error(data.message);
               }
            });
    });
</script>
@stop

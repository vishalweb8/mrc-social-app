@extends('Admin.Master')

@section('content')

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.membershiprequest')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box">
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
                <div class="box-header">
                    <div class="col-md-4">
                        <select class="form-control" name="status" id="filter-status">
                            <option value=''>All Status</option>
                            <option value='1'>Approve</option>
                            <option value='2'>Reject</option>
                            <option value='0'>Pending</option>
                        </select>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    <table id="request-table" class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>Membership Plan</th>
                                <th>{{trans('labels.user')}}</th>
                                <th>{{trans('labels.mobile')}}</th>
                                <th>{{trans('labels.business')}}</th>
                                <th>{{trans('labels.date')}}</th>
                                <th>{{trans('labels.status')}}</th>
                                <th>{{trans('labels.action')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        
        <form id="reasonForm" class="form-horizontal" method="post" action="{{ url('/admin/membershipreject')}}">

            <input type="hidden" value="{{ csrf_token()}}" name="_token">
            <input type="hidden" value="" name="request_id" id="request_id">
            <input type="hidden" value="" name="status" id="status">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="display:none;" id="reasonRejection">Reason for Rejection</h4>
                    <h4 class="modal-title" style="display:none;" id="reasonPending">Reason for Pending</h4>
                    <h4 class="modal-title" style="display:none;" id="commentApprove">Comment for Approve</h4>
                </div>
                <div class="modal-body">
                    <p><textarea name="reasons" id="reasons" placeholder="Enter text" cols="75" rows="5"></textarea></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-default" data-dismiss="modal">
                        Send
                    </button>
                </div>
            </div>
        </form>
    </div>
  </div>
</section><!-- /.content -->
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {        
        $('#request-table').DataTable( {
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('membershipRequest') }}",
                data: function(data) {
                    data.status = $("#filter-status").val();
                }},
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'subscription_plan.name', name: 'subscriptionPlan.name'},
                {data: 'user_name', name: 'user.name'},
                {data: 'user.phone', name: 'user.phone'},
                {data: 'entity_name', name: 'user.singlebusiness.name'},
                {data: 'created_at', name: 'created_at'},
                {data: 'status', name: 'status'},
                {data: 'action', title: 'Action', orderable:false,searchable:false},
            ]
        });
        $("#filter-status").on('change',function(e) {
            $('#request-table').DataTable().ajax.reload();
        });
        // fire event when datatable initialised
        $(document).on( 'init.dt', function ( e, settings ) {
            var api = new $.fn.dataTable.Api( settings );
            var table = '#'+api.table().node().id;
            $(table+'_filter input').unbind();
            $(table+'_filter input').on('change',function(e) {
                $(table).DataTable().search($(this).val()).draw();
            });
        } ); 
    });
    function pendingComment(requestId)
    {
        $('.modal-title').hide();
        $('#reasonPending').show();
        $('.modal').css("display","block");
        $('.fade').css("opacity","1");
        $('#status').val(0);
        $('#request_id').val(requestId);
        $('#reasons').text($('#reason_'+requestId).val());
    }
    function approveComment(requestId)
    {
        $('.modal-title').hide();
        $('#commentApprove').show();
        $('.modal').css("display","block");
        $('.fade').css("opacity","1");
        $('#status').val(1);
        $('#request_id').val(requestId);
        $('#reasons').text($('#reason_'+requestId).val());
    }
    function rejectComment(requestId)
    {
        $('.modal-title').hide();
        $('#reasonRejection').show();
        $('.modal').css("display","block");
        $('.fade').css("opacity","1");
        $('#status').val(2);
        $('#request_id').val(requestId);
        $('#reasons').text($('#reason_'+requestId).val());
    }
// function rejectRequest(requestId)
// {
//     var x = confirm("Are you sure you want to reject this request?");
//     if (x)
//     {
//         $('#reasonRejection').show();
//         $('.modal').css("display","block");
//         $('.fade').css("opacity","1");
//         $('#request_id').val(requestId);
//         $('#status').val(2);
//         return false;
//     }
//     else
//     {
//         return false;
//     }
// }
$(document).ready(function(){
    $(".close").click(function(){
        $('.modal').css("display","none");
        $('.fade').css("opacity","0");
    });
});

</script>
@stop
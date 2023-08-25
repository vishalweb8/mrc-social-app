@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.otp')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="otpList">
                        <thead>
                            <tr>
                                <th>{{trans('labels.phone')}}</th>
                                <th>{{trans('labels.email')}}</th>
                                <th>{{trans('labels.type')}}</th>
                                <th>{{trans('labels.otp')}}</th>
                                <th>{{trans('labels.date')}}</th>
                                <th>
                                    {{trans('labels.headeraction')}} 
                                    <!-- <a href="{{ url('/admin/sendotp')}}/0/all" class="btn bg-green"  data-toggle="tooltip" data-original-title="Send OTP To All" style="float: right;">
                                        <i class="fa fa-check"><b>SEND OTP</b></i> 
                                    </a> -->
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($otpList as $key=>$value)
                            <tr>
                                <td>
                                    ({{$value->country_code}})-{{$value->phone}}
                                </td>
                                <td>
                                    {{$value->email}}
                                </td>
								<td>
                                    {{$value->type}}
                                </td>
								<td>
                                    {{$value->otp}}
                                </td>
                                <td>
                                    {{$value->created_at}}
                                </td>
                                <td>
                                    @can(config('perm.editOTP'))
                                    <a href="{{ url('/admin/editotp') }}/{{Crypt::encrypt($value->id)}}">
                                        <span data-toggle="tooltip" data-original-title="Edit" class='glyphicon glyphicon-edit'></span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.sendOTP'))
                                    <a href="{{ url('/admin/sendotp') }}/{{Crypt::encrypt($value->id)}}/single" >
                                        <i class="fa fa-check" data-toggle="tooltip" data-original-title="Send OTP"></i> 
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.deleteOTP'))
									<a onclick="if(confirm('Are you sure you want to delete ?')) { event.preventDefault(); document.getElementById('otp-delete-{{$value->id}}').submit(); } else return false" href="#">
													<span data-toggle="tooltip" data-original-title="Delete" class="glyphicon glyphicon-remove"></span>
									</a><form id="otp-delete-{{$value->id}}" action="{{ route('otp.destroy',$value->id) }}" method="post" style="display: none;">
													@csrf
													@method('DELETE')
												</form>
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

@can(config('perm.listResetPwdOTP'))
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Reset Password OTP
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="resetPasswordOtp">
                        <thead>
                            <tr>
                                <th>{{trans('labels.phone')}}</th>
                                <th>{{trans('labels.email')}}</th>
                                <th>{{trans('labels.otp')}}</th>
                                <th>{{trans('labels.date')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $key=>$value)
                            <tr>
                                <td>
                                    ({{$value->country_code}})-{{$value->phone}}
                                </td>
                                <td>
                                    {{$value->email}}
                                </td>
								<td>
                                    {{$value->reset_password_otp}}
                                </td>
                                <td>
                                    {{$value->reset_password_otp_date}}
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
@endcan
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('#otpList').DataTable({
            "aaSorting": [],
            //hideEmptyCols: true,
            hideEmptyCols: ['extn', 5], 
        });

        @can(config('perm.listResetPwdOTP'))
        $('#resetPasswordOtp').DataTable({
            hideEmptyCols: true,
            "aaSorting": [[3, 'desc']]
        });
        @endcan
    });
</script>
@stop

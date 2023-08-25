@extends('Admin.Master')

@section('content')
<!-- content   -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.transactions')}}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        List of Transations
                    </h3>                    
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-header">
                    <form id="formSearch" class="form-horizontal" method="post" action="{{ url('/admin/transactions') }}">
                        {{-- <div class="col-md-2">
                            <select name="businesses" class="form-control select2" id="businesses">
                                <option value="">{{ trans('labels.businessName') }}</option>
                                @forelse($businesses as $business)
                                    <option value="{{$business->id}}" {{(isset($postData['businesses']) && $postData['businesses'] == $business->id)?'selected':''}}>{{$business->name}}</option>
                                @empty
                                @endforelse
                            </select>
                        </div> --}}
                        <div class="col-md-2">
                            <select name="plans" class="form-control select2" id="plans">
                                <option value="">{{ trans('labels.plantype') }}</option>
                                @forelse($plans as $plan)
                                    <option value="{{$plan->id}}" {{(isset($postData['plans']) && $postData['plans'] == $plan->id)?'selected':''}}>{{$plan->name}}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="search" value="1">
                            <select class="form-control" name="status">
                                <option value=''>{{trans('labels.status')}}</option>
                                <option value='0' {{(isset($postData['status']) && $postData['status'] == '0')?'selected':''}}>{{trans('labels.Pending')}}</option>
                                <option value='1' {{(isset($postData['status']) && $postData['status'] == '1')?'selected':''}}>{{trans('labels.Paid')}}</option>
                                <option value='2' {{(isset($postData['status']) && $postData['status'] == '2')?'selected':''}}>{{trans('labels.Failed')}}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" class="btn bg-purple" name="searchBtn" id="searchBtn" value="{{trans('labels.filter')}}"/>
                            <a href="{{ url('/admin/transactions') }}">
                                <input type="button" class="btn bg-purple" name="clearBtn" id="clearBtn" value="{{trans('labels.clear')}}"/>
                            </a>
                        </div>
                    </form>
                </div>
                <div class="box-body table-responsive">                    
                    <table id="PaymentTransactions" class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>{{trans('labels.id')}}</th>
                                <th>{{trans('labels.orderId')}}</th>
                                <th>{{trans('labels.businessName')}}</th>
                                <th>{{trans('labels.plantype')}}</th>
                                <th>{{trans('labels.status')}}</th>
                                <th>{{trans('labels.createdat')}}</th>
                                <th>{{trans('labels.lastVerifiedDate')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($PaymentTransactions as $trans)
                            <tr>
                                <td>{{$trans->id}}</td>
                                <td>{{$trans->order_id}}</td>
                                <td>
                                    @if(isset($trans->users))
                                        @if(isset($trans->users['businesses'][0]))
                                            <a href="{{ url('/admin/user/business') }}/{{Crypt::encrypt($trans->users->id)}}">
                                                {{$trans->users['businesses'][0]->name}}
                                            </a>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if(isset($trans->plans))
                                        {{$trans->plans->name}}
                                    @endif
                                </td>  
                                <td>
                                    @if($trans->status == 0)
                                        {{ trans('labels.Pending') }}
                                    @elseif($trans->status == 1)
                                        {{ trans('labels.Paid') }}
                                    @elseif($trans->status == 2)
                                        {{ trans('labels.Failed') }}
                                    @endif
                                </td>
                                <td>{{$trans->created_at}}</td>
                                <td>{{$trans->updated_at}}</td>
                                <!-- <td>
                                    <div class="business_approve">
                                        <button class="btn btn-success" onclick="approved({{$trans->id}})" >
                                            <i class="fa fa-check"></i>&nbsp;Approve
                                        <a href="" class="btn bg-green"  data-toggle="tooltip" data-original-title="Approved" onclick="approved({{$trans->id}})">
                                            <i class="fa fa-check"></i> 
                                        </a>
                                        <a href="#" class="btn btn-danger" data-toggle="tooltip" data-original-title="Rejected" onclick="rejected({{$trans->id}})">
                                            <i class="fa fa-close"></i> 
                                        </a>
                                    </div>
                                </td> -->
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                    <div class="pull-right">{{ $PaymentTransactions->render() }}</div>
                </div>
            </div>
        </div>
    </div>
</section>
@stop
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
    });
</script>
@stop

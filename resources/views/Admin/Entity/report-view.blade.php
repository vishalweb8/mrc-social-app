@extends('Admin.Master')
@section('content')
<link type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
@php 
$rowClass = 'row';
@endphp
<section class="content-header">
    <h1>
        View  Reports
    </h1>    
</section>
<!-- Main content -->
<section class="content">    
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Reports</h3>
                </div><!-- /.box-header -->
                    <div class="box-body">

                        <div class="{{$rowClass}}">                            
                            <label class="col-sm-2 control-label">Report By</label>
                            <div class="col-sm-8">
                                {{ $report->reportBy->name ?? ''}}
                            </div>
                        </div>
                        <div class="{{$rowClass}}">                            
                            <label for="comment" class="col-sm-2 control-label">Comment</label>
                            <div class="col-sm-8">
                                <div class="short-desc">
                                    {!! $report->comment ?? 'N/A' !!}
                                </div>
                            </div>
                        </div>
                        
                        <div class="{{$rowClass}}">                            
                            <label for="reason" class="col-sm-2 control-label">{{trans('labels.reasons')}}</label>
                            <div class="col-sm-8">
                                @foreach($report->reasons as $key => $reason)
                                    <div class="short-desc">
                                        {!! $reason->reason !!}
                                    </div>
                                @endforeach
                            </div>                          
                        </div>

                        <div class="{{$rowClass}}">                            
                            <label for="email_id" class="col-sm-2 control-label">
                                Reported At
                             </label>
                            <div class="col-sm-8">
                                {{ $report->created_at}}
                            </div>
                        </div>
                    </div><!-- /.box-body -->
            </div>
        </div>
    </div>
</section><!-- /.content -->
@stop

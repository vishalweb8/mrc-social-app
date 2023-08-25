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
        View  Site
    </h1>    
</section>
<!-- Main content -->
<section class="content">    
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Site Detail</h3>
                </div><!-- /.box-header -->
                <div class="box-body">

                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-8">
                            {{ $site->name ?? ''}}
                        </div>
                    </div>                        
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-8">
                            <div class="short-desc">
                                {!! $site->description ?? 'N/A' !!}
                            </div>
                        </div>
                    </div>
                    <div class="{{$rowClass}}">
                        <label class="col-sm-2 control-label">{{trans('labels.logo')}}</label>
                        <div class="col-sm-8">
                            <a href="{{$site->logo}}" target="_blank"><img src="{{ $site->logo }}" width="50" height="50"/> </a>
                        </div>
                    </div>                   
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">Category</label>
                        <div class="col-sm-8">
                            {{ $site->assetType->name ?? ''}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">Created By</label>
                        <div class="col-sm-8">
                            {{ $site->createdBy->name ?? ''}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">
                            Created At
                            </label>
                        <div class="col-sm-8">
                            {{ $site->created_at}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">
                            Approval Status
                            </label>
                        <div class="col-sm-8">
                            {{ $site->is_approved}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">Approved By</label>
                        <div class="col-sm-8">
                            {{ $site->approvedBy->name ?? ''}}
                        </div>
                    </div>                        
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">
                            Approved At
                            </label>
                        <div class="col-sm-8">
                            {{ $site->approved_at}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">
                            Visibility
                            </label>
                        <div class="col-sm-8">
                            {{ $site->visibility}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">
                            Is Enable Request
                            </label>
                        <div class="col-sm-8">
                            {{ ($site->is_enable_request) ? 'Yes': 'No'}}
                        </div>
                    </div>              
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">Website</label>
                        <div class="col-sm-8">
                            {{ $site->link ?? ''}}
                        </div>
                    </div> 
                    <div class="{{$rowClass}}">  
                        <?php
                            $socials = (!empty($site)) ? $site->socials->pluck('url','name')->toArray() : [];
                        ?>                          
                        <label class="col-sm-2 control-label">Facebook</label>
                        <div class="col-sm-8">
                            {{ $socials['facebook'] ?? ''}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                         
                        <label class="col-sm-2 control-label">Twitter</label>
                        <div class="col-sm-8">
                            {{ $socials['twitter'] ?? ''}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                         
                        <label class="col-sm-2 control-label">LinkedIn</label>
                        <div class="col-sm-8">
                            {{ $socials['linkedin'] ?? ''}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                    
                        <label class="col-sm-2 control-label">Instagram</label>
                        <div class="col-sm-8">
                            {{ $socials['instagram'] ?? ''}}
                        </div>
                    </div>
                    <div class="{{$rowClass}}">                            
                        <label class="col-sm-2 control-label">
                            Status
                            </label>
                        <div class="col-sm-8">
                            {{ $site->status}}
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div>
        </div>
    </div>
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Join Members</h3>
                    <div class="succcess-msg"></div>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table class="table table-bordered" id="member-table" role="grid"></table>
                </div><!-- /.box-body -->
            </div>
        </div>
    </div>
</section><!-- /.content -->
@stop
@section('script')
<script>
    $(document).ready(function () {
        $('#member-table').DataTable( {
            hideEmptyCols: true,
            processing: true,
            serverSide: true,
            ajax: { 
                url: "{{ route('site.getMembers',$site->id) }}"
            },
            aaSorting: [[0, 'desc']],
            columns: [
                {data: 'id', name: 'id', title: 'Id'},
                {data: 'name', name: 'name', title: 'Name'},
                {data: 'role_name', name: 'role_name', title: 'Role'},
                {data: 'created_date', name: 'created_date', title: 'Joined At'},
                {data: 'action', name: 'action',title: 'Action',orderable:false, searchable:false}
            ]
        });

        $(document).on("click",".delete-site-member",function(e) {
            e.preventDefault();
            var isConfirm = confirm('Are you sure you want to delete?');
            if(isConfirm) {
                var url = $(this).data('url');
                $.ajax({
                    type: "POST",
                    url: url,
                    data: { _token:'{{csrf_token()}}'},
                    success: function( response ) {
                        if(response.status) {
                            successMessage(response.message);
                        } else {
                            showErrorMessage(response.message);
                        }
                        $('#member-table').DataTable().ajax.reload();                            
                    }
                });
            }
        });
        function successMessage(message) {
            $(".succcess-msg").html('<div class="row"><div class="col-md-12"><div class="box-body"><div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button><h4><i class="icon fa fa-check"></i> Success</h4>'+message+'</div></div></div></div>');
        }
    });
</script>
@endsection
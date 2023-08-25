@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

@stop
@section('content')
<section class="content-header">
	<h1>
	Public Website
    @can(config('perm.addPublicWebsite'))
	<a href="" onclick="return confirm('Please go to Business Listing and create Public website from there.')" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;Add Public Website</a>
    @endcan
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
					<div id="subscriptionsplan_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					
						<div class="row"><div class="col-sm-12"><table class="table table-bordered table-striped dataTable no-footer" id="subscriptionsplan" role="grid" aria-describedby="subscriptionsplan_info">
							<thead>
								<tr role="row">
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 70px;" aria-label="Id: activate to sort column ascending">
										Id
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 284px;" aria-label="Name: activate to sort column ascending">
									Business Name
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 203px;" aria-label="No of Month: activate to sort column ascending">User Name
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Website Name
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Template Name
									</th>
									{{-- <th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Template Color Name
									</th> --}}
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Plan Name
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Plan Start Date
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Plan End Date
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Type ({{config('constant.APP_SHORT_NAME')}} / domain)
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Domain name
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 139px;" aria-label="Actions: activate to sort column ascending">
									Status
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 139px;" aria-label="Actions: activate to sort column ascending">
									Actions
									</th>
								</tr>
							</thead>
							<tbody>   
                                       
						@foreach($publicwebsite as $publicwebsiteGet)		


                          <tr role="row" class="odd">

                                <td>
                                    {{$publicwebsiteGet->id}}
                                </td>
                                <td>
                                    <a href="{{url('admin/allentity')}}">{{$publicwebsiteGet->businessName->name ?? ''}}</a>
                                </td>
                                <td>
                                   {{$publicwebsiteGet->businessName->user->name ?? ''}} 
                                </td>
                                <td>
                           			 {{-- <p>https://ryuva.club/website/{{$publicwebsiteGet->website_name}}</p>  --}}
                                       {{$publicwebsiteGet->website_name}}                                 
                                </td>
                                <td>
                           			{{$publicwebsiteGet->templetName->template_name}}
                                                                        
                                </td>
                              {{--   <td>
                           			{{$publicwebsiteGet->pw_template_color_id}}
                                                                        
                                </td> --}}
                                <td>
                           			{{$publicwebsiteGet->plantName->pw_plan_name}}
                                                                        
                                </td>
                                <td>
	                                @if($publicwebsiteGet->pw_plan_start_date)
	                                	@php
	                                		$startDate = date('d/m/Y', strtotime($publicwebsiteGet->pw_plan_start_date));
	                                	@endphp
	                           			{{$startDate}}
                           			@endif
                                                                        
                                </td>
                                <td>
                                	@if($publicwebsiteGet->pw_plan_end_date)
	                                	@php
	                                		$endDate = date('d/m/Y', strtotime($publicwebsiteGet->pw_plan_end_date));
	                                	@endphp
	                           			{{$endDate}}
                           			@endif
                           			{{-- {{$publicwebsiteGet->pw_plan_end_date}} --}}
                                                                        
                                </td>
                                <td>
                                	@if($publicwebsiteGet->pw_type == 1)
                                		<p>{{config('constant.APP_SHORT_NAME')}}</p>
                                	@else
                                		<p>Domain</p>
                                	@endif	
                                                                        
                                </td>
                                <td>
                           			{{$publicwebsiteGet->pw_domain}}
                                                                        
                                </td>

                       			<td>
                                    @can(config('perm.updateStatusPublicWebsite'))
                       				<select class="form-control status" name="status" data-id={{$publicwebsiteGet->id}}>
                       					<option value="0" {{ $publicwebsiteGet->status == 0 ? 'selected':'' }}>Pending</option>
                       					<option value="1" {{ $publicwebsiteGet->status == 1 ? 'selected':'' }}>Submitted</option>
                       					<option value="2" {{ $publicwebsiteGet->status == 2 ? 'selected':'' }}>Live</option>
                       					<option value="3" {{ $publicwebsiteGet->status == 3 ? 'selected':'' }}>Paused</option>
                       					<option value="4" {{ $publicwebsiteGet->status == 4 ? 'selected':'' }}>Removed</option>
                       				</select>
                                    @endcan
                       			</td>
                                <td>
                                    @can(config('perm.editPublicWebsite'))
                                    <a href="{{ url('admin/allpublicwebsite/edit') }}/{{Crypt::encrypt($publicwebsiteGet->id)}}">
                                        <span data-toggle="tooltip" data-original-title="Edit" class="glyphicon glyphicon-edit"></span>

                                    </a>&nbsp;&nbsp;
                                    @endcan
                                    @can(config('perm.deletePublicWebsite'))
                                    <a onclick="return confirm('Are you sure you want to delete ?')" href="{{ url('admin/allpublicwebsite/remove') }}/{{Crypt::encrypt($publicwebsiteGet->id)}}">
                                        <span data-toggle="tooltip" data-original-title="Delete" class="glyphicon glyphicon-remove"></span>
                                    </a>&nbsp;&nbsp;
                                    @endcan
                                </td>
                           </tr>
                           @endforeach
                       </tbody>
							
				</table>
			</div>
		</div>
		
	</div>
				</div><!-- /.box-body -->
			</div>
			<!-- /.box -->
			
			</div><!-- /.col -->
			</div><!-- /.row -->
		</section>
		@stop
		@section('script')
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

		<script type="text/javascript">
			$(document).ready(function() {
        $('#subscriptionsplan').DataTable({
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
        var id = $(this).data('id');
        var value = this.value;
        console.log(id)
    // var status = $(this).prop('checked') === true ? 1 : 0; ;  
    // var id =  $(this).data('id');
    // var checked = false;
    // var statusChange;
    $.ajax({
               type:'POST',
               url:"{{route('PublicWebsiteController.status')}}",
               data: {id:id, value:value},
               success:function(data) {
                  toastr.success(data.message);

               }
            });
    });


</script>
@stop
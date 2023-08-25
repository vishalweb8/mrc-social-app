@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
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
<section class="content-header">
	<h1>
	Public Website Templates
    @can(config('perm.addPublicWebsiteTemplate'))
	<a href="{{route('PublicWebsiteTetemplets.create')}}" class="btn bg-purple pull-right"><i class="fa fa-plus"></i>&nbsp;Add Public Website Template</a>
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
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Name: activate to sort column ascending">
									Template Name
									</th>
									<th>
									Template Theme
									</th>
									
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
									Preview Image
									</th>
									<th class="sorting" tabindex="0" aria-controls="subscriptionsplan" rowspan="1" colspan="1" style="width: 50px;" aria-label="Price: activate to sort column ascending">
									Status
									</th>
									<th>
									Actions
									</th>
								</tr>
							</thead>
							<tbody>   
                                       
						@foreach($PublicWebsiteTemplets as $PublicWebsiteTempletsGet)		
                          <tr role="row" class="odd">
                          	<td>
                          		{{$PublicWebsiteTempletsGet->id}}
                          	</td>
                          	<td>
                          		{{$PublicWebsiteTempletsGet->template_name}}
                          		
                          	</td>
							<td>
                          		{{$PublicWebsiteTempletsGet->template_theme}}
                          		
                          	</td>
                          	<td>
                          		@php
                                    $s3url =   Config::get('constant.s3url');
                                @endphp
                          		@if($PublicWebsiteTempletsGet->preview_image) 
                                        <img style="cursor: pointer;" data-toggle='modal' data-target='#{{$PublicWebsiteTempletsGet->id.substr(trim($PublicWebsiteTempletsGet->preview_image), 0, -10)}}' src="{{ $s3url.Config::get('constant.PUBLIC_WEBSITE_ORIGINAL_IMAGE').$PublicWebsiteTempletsGet->preview_image }}" width="50" height="50" class="img-circle"/>
                                        <div class='modal modal-centered fade image_modal' id='{{$PublicWebsiteTempletsGet->id.substr(trim($PublicWebsiteTempletsGet->preview_image), 0, -10)}}' role='dialog' style='vertical-align: center;'>
                                            <div class='modal-dialog modal-dialog-centered'>
                                                <div class='modal-content' style="background-color:transparent;">
                                                    <div class='modal-body'>
                                                    <center>
                                                        <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                                        <img src="{{ $s3url.Config::get('constant.PUBLIC_WEBSITE_ORIGINAL_IMAGE').$PublicWebsiteTempletsGet->preview_image }}" style='width:100%; border-radius:5px;' title="{{$PublicWebsiteTempletsGet->preview_image}}" />
                                                    <center>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <img src="{{ url('images/default.png') }}" width="50" height="50" class="img-circle"/>
                                    @endif
                          		

                          		{{-- <img style="cursor: pointer;" data-toggle='modal' data-target='#{{$PublicWebsiteTempletsGet->id.substr(trim($PublicWebsiteTempletsGet->preview_image), 0, -10)}}' src="{{ Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$PublicWebsiteTempletsGet->preview_image) }}" height="40" width="40" title="{{$PublicWebsiteTempletsGet->preview_image}}" class="img-circle" /> --}}

                          	</td>
                          	<td>
                                @can(config('perm.updateStatusPublicWebsiteTemplate'))
                                    <label class="switch">
                                        <input type="checkbox" name="status" class="status" {{ $PublicWebsiteTempletsGet->status == 1 ? 'checked':''  }} data-id="{{$PublicWebsiteTempletsGet->id}}" autocomplete="off" value="{{ $PublicWebsiteTempletsGet->status }}">
                                        <span class="slider round"></span>
                                    </label>
                                @endcan                             
                          	</td>
                          	<td>
                                @can(config('perm.editPublicWebsiteTemplate'))
                          		    <a href="{{ url('admin/allpublicwebsitetetemplets/edit') }}/{{Crypt::encrypt($PublicWebsiteTempletsGet->id)}}">
                                        <span data-toggle="tooltip" data-original-title="Edit" class="glyphicon glyphicon-edit"></span>

                                    </a>&nbsp;&nbsp;
                                @endcan
                                @can(config('perm.deletePublicWebsiteTemplate'))
                                    <a onclick="return confirm('Are you sure you want to delete ?')" href="{{ url('admin/allpublicwebsitetetemplets/remove')}}/{{Crypt::encrypt($PublicWebsiteTempletsGet->id)}}">
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
    var status = $(this).prop('checked') === true ? 1 : 0; ;  
    var id =  $(this).data('id');
    var checked = false;
    var statusChange;
    $.ajax({
               type:'POST',
               url:"{{route('PublicWebsiteTetemplets.status')}}",
               data: {id:id, status:status},
               success:function(data) {
                  console.log(data)
                  toastr.success(data.message);

               }
            });
    });
</script>
@stop





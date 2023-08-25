@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

@stop
@section('content')
<section class="content-header">
	<h1>
	Public Website Review
	
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
					<div id="public_review_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-bordered table-striped dataTable no-footer" id="public_review" role="grid" aria-describedby="public_review_info">
									<thead>
										<tr role="row">
											<th class="sorting" tabindex="0" aria-controls="public_review" rowspan="1" colspan="1" style="width: 70px;" aria-label="Id: activate to sort column ascending">
												Id
											</th>
											<th class="sorting" tabindex="0" aria-controls="public_review" rowspan="1" colspan="1" style="width: 284px;" aria-label="Name: activate to sort column ascending">
											Business Name
											</th>
											<th class="sorting" tabindex="0" aria-controls="public_review" rowspan="1" colspan="1" style="width: 203px;" aria-label="No of Month: activate to sort column ascending">User Name
											</th>
											<th class="sorting" tabindex="0" aria-controls="public_review" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
											Email
											</th>
											<th class="sorting" tabindex="0" aria-controls="public_review" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
											Mobile Number
											</th>
											<th class="sorting" tabindex="0" aria-controls="public_review" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
											Rating
											</th>
											<th class="sorting" tabindex="0" aria-controls="public_review" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
											Comment
											</th>
											<th class="sorting" tabindex="0" aria-controls="public_review" rowspan="1" colspan="1" style="width: 110px;" aria-label="Price: activate to sort column ascending">
											Date
											</th>									
										</tr>
									</thead>
									<tbody>   
											
										@foreach($reviews as $review)		


										<tr role="row" class="odd">

											<td>
												{{$review->id}}
											</td>
											<td>
												{{$review->business->name ?? ''}}
											</td>
											<td>
											{{$review->name}} 
											</td>
											<td>
												{{$review->email}}                                
											</td>
											<td>
												{{$review->mobile_number}}
											</td>
											<td>
												{{$review->rating}}
											</td>
											<td>
												{{$review->message}}
											</td>
											<td>
												{{$review->created_at->format("Y-m-d H:i")}}                                                                       
											</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</section>
@stop
@section('script')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('#public_review').DataTable({
                hideEmptyCols: true,
				"aaSorting": []
			});
		});
	</script>
@stop
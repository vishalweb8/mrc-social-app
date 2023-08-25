@extends('Admin.Master')
@section('header')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

@stop
@section('content')
<section class="content-header">
	<h1>
	{{trans('labels.send_mail')}}
    @can(config('perm.addSendEmail'))
	<a href="{{ route('sendMail.create') }}" class="btn bg-purple pull-right">{{trans('labels.send_mail')}}</a>
    @endcan
	</h1>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<div class="box-body table-responsive">
					<div id="send_mail_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-bordered table-striped dataTable no-footer" id="send_mail" role="grid" aria-describedby="send_mail_info">
									<thead>
										<tr role="row">
											<th>
												Id
											</th>
											<th >
												Type
											</th>											
											<th>
												Start Id
											</th>											
											<th>
												End Id
											</th>
											<th>
												Subject
											</th>
											<th>
												Number Of Sent
											</th>
											<th>
												Created Date
											</th>
											<th>Actions</th>									
										</tr>
									</thead>
									<tbody>   
											
										@foreach($mails as $mail)		


										<tr role="row" class="odd">

											<td>
												{{$mail->id}}
											</td>
											<td>
												{{$mail->type}}
											</td>
											<td>
												{{$mail->start_id}} 
											</td>
											<td>
												{{$mail->end_id}}                                
											</td>
											<td>
												{{$mail->subject}}
											</td>
											<td>
												{{$mail->number_of_sent}}
											</td>
											<td>
												{{$mail->created_at->format("Y-m-d H:i")}} 
											</td>
											<td style="text-align: center;">
                                                @can(config('perm.editSendEmail'))
                                                <a href="{{ route('sendMail.edit',$mail->id) }}">
													<span data-toggle="tooltip" data-original-title="Edit" class="glyphicon glyphicon-edit"></span>

												</a>&nbsp;&nbsp;
                                                @endcan
                                                @can(config('perm.deleteSendEmail'))
												<a onclick="if(confirm('Are you sure you want to delete ?')) { event.preventDefault(); document.getElementById('mail-send-{{$mail->id}}').submit(); } else return false" href="#">
													<span data-toggle="tooltip" data-original-title="Delete" class="glyphicon glyphicon-remove"></span>
												</a>&nbsp;&nbsp;
												<form id="mail-send-{{$mail->id}}" action="{{ route('sendMail.destroy',$mail->id) }}" method="post" style="display: none;">
													@csrf
													@method('DELETE')
												</form>
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
			$('#send_mail').DataTable({
                hideEmptyCols: true,
				"aaSorting": [
                    [0, 'desc']
                ],
				columnDefs: [{ 'orderable': false, 'targets': 7 }]
			});
		});
	</script>
@stop
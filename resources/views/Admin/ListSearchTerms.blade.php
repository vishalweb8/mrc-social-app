@extends('Admin.Master')

@section('content')
<!-- content push wrapper -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>

        {{trans('labels.searchterms')}}
       
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <table class='table table-bordered table-striped' id="searchTermList">
                        <thead>
                            <tr>
                                <th>{{trans('labels.searchterm')}}</th>
                                <th>{{trans('labels.user')}}</th>
                                <th>{{trans('labels.city')}}</th>
                                <th>{{trans('labels.count')}}</th>
                                <th>{{trans('labels.date')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($searchTermList as $key=>$value)
                            <tr>
                                <td>
                                    {{$value->search_term}}
                                </td>
                                <td>
									@if(!empty($value->user_id))
                                        <a href="{{ url('/admin/edituser') }}/{{Crypt::encrypt($value->user_id)}}" target="_blank">
										{{$value->user->name ?? ''}}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    {{$value->city}}
                                </td>
                                <td>
                                    {{$value->result_count}}
                                </td>
								<td>
                                    {{$value->created_at->format("Y-m-d H:i")}}
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
<script type="text/javascript">
    $(document).ready(function() {
        $('#searchTermList').DataTable({
            hideEmptyCols: true,
           "aaSorting": []
        });
    });
</script>
@stop

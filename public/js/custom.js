$(document).ready(function() {
    $.extend(true, $.fn.dataTable.defaults, {
        lengthChange: true,
    
    });
    $(document).on( 'init.dt', function ( e, settings ) {
        var api = new $.fn.dataTable.Api( settings );
        var table = '#'+api.table().node().id;
        $(table+'_filter input').unbind();
        $(table+'_filter input').on('change',function(e) {
            $(table).DataTable().search($(this).val()).draw();
        });
    } );
});

function showSuccessMessage(message) {
    $(".ajex-message").html('<div class="row"><div class="col-md-12"><div class="box-body"><div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button><h4><i class="icon fa fa-check"></i> Success</h4>'+message+'</div></div></div></div>');
}

function showErrorMessage(message) {
    $(".ajex-message").html('<div class="row"><div class="col-md-12"><div class="box-body"><div class="alert alert-error alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button><h4><i class="icon fa fa-check"></i> Error</h4>'+message+'</div></div></div></div>');
}
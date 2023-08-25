<script type="text/javascript">
    $(document).ready(function() {
        $(document).on("click",".delete-asset-type",function(e) {
            e.preventDefault();
            var isConfirm = confirm('Are you sure? you want to delete this.');
            if(isConfirm) {
                $("#delete-asset-form").attr('action',$(this).data('url'));
                $("#delete-asset-form").submit();
            }            
        });
        $('#asset_type_filter input').unbind();
        $("#asset_type_filter input").on('change',function(e) {
            $('#asset_type').DataTable().search($(this).val()).draw();
        });
    });
</script>
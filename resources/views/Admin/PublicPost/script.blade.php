<!-- <script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script> -->
<!-- Include tags in input box -->
<script src="{{ asset('js/jquery.caret.min.js') }}"></script>
<script src="{{ asset('js/jquery.tag-editor.js') }}"></script>
<script type="text/javascript">
	//CKEDITOR.replace('content');
	$('#post_keywords').tagEditor({
        placeholder: 'Enter Post Keywords ...',
    });

	$('#source').change(function() {
		console.log(this.value);
		$(".external-section input").val('');
		if(this.value == 'external') {
			$(".external-section").show();
		} else {
			$(".external-section").hide();			
		}
	});

    $('#images').change(function(){
        if (this.files.length + $(".post-images").length > 4) {
            $('#images').val('');
            alert("You can't upload greater than 4 images");
        }
    });

    $('.delete-post-image').click(function(){
        let id = $(this).data('id');
        $(this).closest(".post-images").remove();
        $(".post-images-preview").append("<input  name='delete_image_ids[]' type='hidden' value='"+id+"'>");
        
    });
</script>
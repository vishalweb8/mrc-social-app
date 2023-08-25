<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
<script>
	$(document).ready(function() {
		$('#business_id').select2({
			placeholder: 'Select Business',
			ajax: {
				url: '{{ url("admin/auto-complete-business") }}',
				dataType: 'json',
				delay: 250,
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				processResults: function (data) {
					return {
						results: data
					};
				},
				cache: true
			}
		});
	});
</script>
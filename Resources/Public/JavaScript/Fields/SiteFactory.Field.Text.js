jQuery(document).ready(function() {
	SiteFactory.Form.GetAllInstances().each(function(formInstance) {
		var textFields = formInstance.getFieldsByFieldType('text');

		for(var index in textFields) {
			if (textFields.hasOwnProperty(index)) {
				/*
				 * The event fires 500ms after the last action done on the input. It
				 * means that if you write a letter 200ms after the first letter,
				 * the event will eventually fire 500ms later (700ms after the first
				 * one).
				 */
				jQuery(textFields[index].input).on(
					'change keyup',
					{field: textFields[index]},
					function(event) {
						var field = event.data.field;
						var input = field.input;
						if (jQuery(input).val() == input.lastVal) return;

						clearInterval(jQuery(input).data('evalTimer'));

						jQuery(input).data('evalTimer', setInterval(
							function() {
								clearInterval(jQuery(input).data('evalTimer'));
								input.lastVal = jQuery(input).val();

								field.validate();
							},
							500
						));
					}
				);
			}
		}
	});
});
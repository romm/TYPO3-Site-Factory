jQuery(document).ready(function() {
	function changePreviewColor(field, color) {
		var previewElement = field.findElement('.color-picker-preview .color-picker-preview-cube');
		previewElement.css('background-color', color);
	}

	SiteFactory.Form.GetAllInstances().each(function(formInstance) {
		var fields = formInstance.getFieldsByType('color_picker');

		for(var index in fields) {
			if (fields.hasOwnProperty(index)) {
				fields[index].fillColorPickerField = function(value) {
					this.input.val(value);
					changePreviewColor(this, value);
				};

				jQuery(fields[index].input).on(
					'change, keyup',
					{field: fields[index]},
					function(event) {
						var field = event.data.field;
						changePreviewColor(field, field.input.val())
					}
				);
			}
		}
	});
});
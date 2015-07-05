jQuery(document).ready(function() {
	SiteFactory.Form.GetAllInstances().each(function(formInstance) {
		var selectFields = formInstance.getFieldsByFieldType('select');
		for(var index in selectFields) {
			if (selectFields.hasOwnProperty(index)) {
				jQuery(selectFields[index].input).on(
					'change',
					{field: selectFields[index]},
					function(event) {
						event.data.field.validate();
					}
				);
			}
		}
	});
});
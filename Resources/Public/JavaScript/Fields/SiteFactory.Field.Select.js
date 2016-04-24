jQuery(document).ready(function() {
	var instances = SiteFactory.Form.GetAllInstances();
	for (var i = 0; i < instances.length; i++) {
		var formInstance = instances[i];
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
	}
});
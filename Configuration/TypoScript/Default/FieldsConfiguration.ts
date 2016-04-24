module.tx_sitefactory {
	fields {
		# A list containing all the model sites existing on the TYPO3 instance.
		modelSite {
			type = select
			position = first
			label = form.field.label.model_site
			hint = form.field.hint.model_site
			options = Romm\SiteFactory\Form\FieldsConfigurationPresets->getModelSitesList
			hideInSiteModification = 1
		}

		# The name of the new site.
		siteTitle {
			type = text
			position = after:modelSite
			label = form.field.label.site_name
			hint = form.field.hint.site_name
			placeholder = form.field.default_value.site_name
			defaultValue = form.field.default_value.site_name
			hideInSiteModification = 1
			validation {
				# We want at least one character for the name.
				noEmpty {
					validator = Romm\SiteFactory\Form\Validation\NotEmptyValidator
				}
			}
		}
	}
}
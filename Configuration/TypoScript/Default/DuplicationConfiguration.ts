module.tx_sitefactory {
	duplicationProcesses {
		# Will process the entire site's duplication: all the pages, sub pages and contents.
		pagesDuplication {
			class       = Romm\SiteFactory\Duplication\Process\PagesDuplicationProcess
			label       = duplication_process.pages_duplication
		}

		# Used after "pagesDuplication", it will provide an array containing a full association
		# between the uids of the model site's pages and the uids of the duplicated site's pages.
		treeUidAssociation {
			class       = Romm\SiteFactory\Duplication\Process\TreeUidAssociationProcess
			label       = duplication_process.pages_association
		}

		# Creates a new "sys_filemounts" record.
		#
		# Available settings:
		#  - path:             Path of the folder created on the server.
		#                      If none given, "user_upload" is used.
		#  - newRecordName:    Will save the new "sys_filemounts" record's uid at this index.
		#					   It can then be used later (e.g. link this record to a backend user).
		#                      If none is given, "fileMountUid" is used.
		sysFileMounts {
			class       = Romm\SiteFactory\Duplication\Process\SysFileMountsProcess
			label       = duplication_process.mount_point_creation
			settings {
				path = user_upload/
				createdRecordName = fileMountUid
			}
		}

		# Creates a new "be_groups" record.
		#
		# Available settings:
		#  - modelUid:             Required!
		#                          Uid of the backend user group model, which will be duplicated for the new site.
		#  - sysFileMountUid:      The uid of the file mount which will be linked to the backend user group.
		#                          Can be an integer, or "data:foo" where foo refers to the value of "settings.createdRecordName"
		#                          for the file mount creation process (default value is "fileMountUid").
		#  - createdRecordName:    Will save the new "be_group" record's uid at this index.
		#                          It can then be used later (e.g. link this record to a backend user).
		#                          If none is given, "backendUserGroupUid" is used.
		backendUserGroupCreation {
			class       = Romm\SiteFactory\Duplication\Process\BackendUserGroupCreationProcess
			label       = duplication_process.backend_usergroup_creation
			settings {
				modelUid =
				sysFileMountUid = data:fileMountUid
				createdRecordName = backendUserGroupUid
			}
		}

		backendUserCreation {
			class       = Romm\SiteFactory\Duplication\Process\BackendUserCreationProcess
			label       = duplication_process.backend_user_creation
			settings {
				modelUid =
				sysFileMountUid = data:fileMountUid
				createdRecordName = backendUserUid
			}
		}

		uploadedFiles {
			class       = Romm\SiteFactory\Duplication\Process\UploadedFilesProcess
			label       = duplication_process.uploaded_files
			usedInSiteModification = 1
		}

		backendConstantsAssignation {
			class       = Romm\SiteFactory\Duplication\Process\BackendConstantsAssignationProcess
			label       = duplication_process.backend_constants_assignation
			usedInSiteModification = 1
		}

		saveSiteConfiguration {
			class       = Romm\SiteFactory\Duplication\Process\SaveSiteConfigurationProcess
			label       = duplication_process.save_site_configuration
			usedInSiteModification = 1
		}
	}

	duplication {
		10  < module.tx_sitefactory.duplicationProcesses.pagesDuplication
		20  < module.tx_sitefactory.duplicationProcesses.treeUidAssociation
		30  < module.tx_sitefactory.duplicationProcesses.sysFileMounts
		40  < module.tx_sitefactory.duplicationProcesses.backendUserGroupCreation
		40.settings.modelUid = 13
		50  < module.tx_sitefactory.duplicationProcesses.backendUserCreation
		50.settings.modelUid = 1
		60  < module.tx_sitefactory.duplicationProcesses.uploadedFiles
		70  < module.tx_sitefactory.duplicationProcesses.backendConstantsAssignation
		80  < module.tx_sitefactory.duplicationProcesses.linkToPageMedia
		90  < module.tx_sitefactory.duplicationProcesses.LinkToPageBackendLayout
		100 < module.tx_sitefactory.duplicationProcesses.saveSiteConfiguration
	}
}
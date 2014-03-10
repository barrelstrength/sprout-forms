
# Insert Data Into Sprout Forms Forms table
# ------------------------------------------------------------

INSERT INTO `craft_sproutforms_forms` (`id`, `name`, `handle`, `redirectUri`, `submitButtonType`, `submitButtonText`, `email_distribution_list`, `notification_reply_to`, `notification_subject`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,'Contact','contact','sproutforms/field-by-field','input','',NULL,NULL,NULL,'2013-11-12 21:38:55','2014-03-03 03:22:40','ff67708c-dee4-4242-9f9e-1d5c221763a7');


# Insert Data Into Sprout Forms Fields table
# ------------------------------------------------------------

INSERT INTO `craft_sproutforms_fields` (`id`, `formId`, `name`, `handle`, `context`, `instructions`, `translatable`, `type`, `settings`, `validation`, `sortOrder`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(2,1,'Name','formId1_fullName','global','',0,'PlainText','{\"placeholder\":\"\",\"maxLength\":\"\",\"multiline\":\"\",\"initialRows\":\"4\"}','required',NULL,'2013-11-15 13:50:20','2014-03-03 03:15:40','2554ab82-1937-485b-abdc-4ed285782f19'),
	(4,1,'Email','formId1_email','global','',0,'PlainText','{\"placeholder\":\"\",\"maxLength\":\"\",\"multiline\":\"\",\"initialRows\":\"2\"}','required',NULL,'2013-11-15 14:34:57','2014-03-03 03:14:30','eeaed567-ccc7-4e5d-935d-298605395b12'),
	(8,1,'Message','formId1_message','global','',0,'PlainText','{\"placeholder\":\"\",\"maxLength\":\"\",\"multiline\":\"1\",\"initialRows\":\"4\"}','required',NULL,'2014-03-03 03:14:45','2014-03-03 03:14:45','c81af8ad-31bc-45f7-beeb-1a6ee6a0c136');


# Add Contact Form Fields to the Content Table
# ------------------------------------------------------------

ALTER TABLE `craft_sproutforms_content` 
ADD `formId1_message` text NULL AFTER `formId`;

ALTER TABLE  `craft_sproutforms_content` 
ADD `formId1_email` text NULL AFTER `formId`;

ALTER TABLE `craft_sproutforms_content` 
ADD `formId1_fullName` text NULL AFTER `formId`;

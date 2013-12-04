INSERT INTO `craft_sproutforms_forms` (`id`, `name`, `handle`, `email_distribution_list`, `dateCreated`, `dateUpdated`, `uid`) VALUES
(1, 'Example', 'example', NULL, '2013-11-12 21:38:55', '2013-11-15 12:25:33', 'ff67708c-dee4-4242-9f9e-1d5c221763a7'),
(2, 'Example 2', 'example2', NULL, '2013-11-15 14:50:48', '2013-11-15 14:50:48', '8529be2f-cafa-4d27-9b15-7676952554bf');


INSERT INTO `craft_sproutforms_fields` (`id`, `formId`, `name`, `handle`, `instructions`, `translatable`, `type`, `settings`, `validation`, `dateCreated`, `dateUpdated`, `uid`) VALUES
(2, 1, 'Example Dropdown', 'formId1_exampleDropdown', 'This is an example dropdown field.', 0, 'Dropdown', '{"options":[{"label":"Please Select","value":"","default":""},{"label":"Item 1","value":"Item 1","default":""},{"label":"Item 2","value":"Item 2","default":""},{"label":"Item 3","value":"Item 3","default":""}]}', 'required', '2013-11-15 13:50:20', '2013-11-16 12:22:29', '2554ab82-1937-485b-abdc-4ed285782f19'),
(4, 1, 'Example Plain Text', 'formId1_examplePlainText', 'This is an example of a text input.', 0, 'PlainText', '{"placeholder":"test","maxLength":"4","multiline":"","initialRows":"2"}', 'required,url', '2013-11-15 14:34:57', '2013-11-15 18:24:47', 'eeaed567-ccc7-4e5d-935d-298605395b12'),
(7, 2, 'Example Plain Text', 'formId2_examplePlainText', 'Example Plain Text', 0, 'PlainText', '{"placeholder":"","maxLength":"","multiline":"","initialRows":"4"}', 'required,numerical', '2013-11-15 17:00:51', '2013-11-15 17:41:48', 'ca47cdee-546e-4fac-86e1-4d4262f54afc');


ALTER TABLE  `craft_sproutforms_content` ADD  `formId1_exampleDropdown` TEXT NULL AFTER  `formId`;
ALTER TABLE  `craft_sproutforms_content` ADD  `formId1_examplePlainText` TEXT NULL AFTER  `formId`;
ALTER TABLE  `craft_sproutforms_content` ADD  `formId2_examplePlainText` TEXT NULL AFTER  `formId`;
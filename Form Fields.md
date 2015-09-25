# Sprout Forms Field API
Our simple yet comprehensive **Field API** allows you to register your field types with Sprout Forms and enable front end rendering support.

By default, only a subset field types available to elements in the Control Panel, are supported by Sprout Forms. The reason for this is that, many field types are complex and Craft makes no effort to help you render those fields on the front end, they are only supported in the Control Panel.

Here is where the Field API comes into play. By extending `SproutFormsBaseFormField` in _your own plugin_, you can begin adding front end rendering for your own field type or any field type you'd like to attach to your Sprout Form.

# Overview
When creating a field to add support for front end rendering, you will do the following:

1. Create a class that extends `SproutFormsBaseFormField`
2. Add the `getInputHtml()` to your your **field class**
3. Add the `registerSproutFormsFields()` in your **plugin class**

## Field Class
The field class naming convesion we recommend is: `{PluginHandle}{FieldType}{FormField}`.

An example would be: `SproutFieldsPlainTextFormField`;

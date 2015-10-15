# Sprout Forms Field API
Our simple, yet comprehensive **Field API** allows you to add front end rendering support for back end **Field Types**

By default, only a subset of field types available to elements in the Control Panel, are supported by Sprout Forms. The reason for this is that, many field types are complex and Craft makes no effort to help you render those fields on the front end. They are only supported in the Control Panel.

Here is where the Field API comes into play. By extending `SproutFormsBaseField` in _your own plugin_, you can begin adding front end rendering support for your own field type or any field types you'd like to attach to your Sprout Form.

# Overview
To create a field that extends our API, you'll need to do the following:

1. Create a class that extends `SproutFormsBaseField`
2. Add the `getInputHtml()` to your your **field class**
3. Add the `registerSproutFormsFields()` in your **plugin class**

## Field Class
The _field_ class naming convesion we recommend is: `{PluginHandle}{FieldType}{Field}`.

## getInputHtml()
Like a back end _field type_, your front end _field_ gets to decide what **html** to render to capture user input.

#### Signature
```php
public function getInputHtml($field, $value, $settings, array $renderingOptions = null)
```

```
$field            > The FieldModel we're providing front end rending for
$value            > The value associated with that field type
$settings         > The settings associated with that field type
$renderingOptions > Options passed via displayForm() or displayField
```

In addition to the arguments, you should make sure to return a `\Twig_Markup` object from this method so that your html is not escaped.

#### beginRendering()
This method should be called just before your render your front end field template inside of `getInputHtml()`

This is due to how we're allowing the user to override `form`, `tab`, and `field` templates for style customization.

Not calling `beginRendering()` could cause your template to not be found.

#### endRendering()
This method should be called just after you finish rendering your front end field template.

Not calling `endRendering()` could cause your template or Sprout Forms own templates to not be found.

#### getTemplatesPath()
Because Sprout Forms allows the user to customize/override the default templates (`form.html`, `tab.html`, `field.htmnl`, `errors.htnl`), we need to switch the template path a few times during rendering of all fields.

This method is your chance to make sure your templates are found when your field is rendered.

From this method, you should return the absolute path to your templates folder so that we can switch to it if you're rendering a template via `getInputHtml()`

---

If we follow the instructions above, our `getInputHtml()` and `getTemplatesPath()` might looks something like this:

```php
public function getInputHtml($field, $value, $settings, array $renderingOptions = null)
{
    $this->beginRendering();

    $rendered = craft()->templates->render(
        'plaintext/input',
        array(
            'name'             => $field->handle,
            'value'            => $value,
            'field'            => $field,
            'settings'         => $settings,
            'renderingOptions' => $renderingOptions
        )
    );

    $this->endRendering();

    return TemplateHelper::getRaw($rendered);
}

public function getTemplatesPath()
{
    return dirname(__FILE__).'/myplugin/templates/';
}
```

## parent::getFieldVariables()
You can use this method to access the `twig global context` and any variables added via `craft.sproutForms.addFieldVariables()`.

This is useful when you need to use those variables in your `getInputHtml()` method to render values at run time.

---

The user can add variables before calling `displayForm()`, `displayTab()`, or `displayField()` like so:

```twig
{% set entry = craft.entries.limit(1)first() %}
{% do craft.sproutForms.addFieldVariables({entry: entry }) %}

{{ craft.sproutForms.displayForm('handle') }}
```

Then, inside your field, you could call `parent::getFieldVariables()` to access them.

## Examples
If you'd like to take a peek at how we're using this API, you can look at Sprout Forms integrations folder.

All the default fields that we provide front end rendering for, are using this very API:)

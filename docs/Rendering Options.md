# Sprout Forms Rendering Options

Rendering options allow you to customize form and field output without having to override the templates, unless your needs extend beyond what is currently supported and documented below.

### Overview
Rendering options should be passed as a twig object to either the `displayForm()` or `displayField()` methods.

### Supported Options
Most of these options are available to both, the `<form>` tag and the `<input>` tag.

| Option  | Type     | Default                     | Description                                                             |
|---------|----------|-----------------------------|-------------------------------------------------------------------------|
| `id`    | `string` | `formHandle | fieldHandle`  | The id to assign to the form or input tag                               |
| `class` | `string` | | A space separated list of classes to apply to the form or input tag     |
| `errorClass` | `string` | | A space separated list of classes to apply to the form or input tag when errors are found |
| `data` | `{}` | | An object (associative array) of data attributes to set on the form or input tag

In the following sections, we document how these options can be used, the effect they will have on the rendered output, and the context they should be used.

#### displayField()
The `displayField()` method accepts rendering options for the field only and all options are passed as top level key/value pairs.

```twig
{% set options = {
    "class": "field field-text",
    "errorClass": "field-has-error",
    "required": true,
    "data": {
        "hidden": "true",
    }
} %}

{{ displayField("fieldHandle", options) }}
```

```html
<input type="text" class="field field-text" data-hidden="true" required value="" />
```

#### displayForm()
The `displayForm()` accepts rendering for itself (form tag) and for its associated fields. If you want to provide rendering options for your fields, you must create a `fields` object and use the field handle to identify the fields that the containing rendering options should be applied to.

```twig

{% set options = {
    "id": "myform",
    "class": "form-class form-class-customized",
    "errorClass": "form-has-error",
    "fields": {
        "animalField": {
            "id": "myfield",
            "class": "field-class field-class-text",
            "errorClass": "field-has-error",
            "data": {
                "hidden": "true",
            }
        }
    }
} %}

{{ displayForm("formHandle", options) }}

```

```html
<form method="post" id="formHandle">
  <input type="text" class="field field-text" data-hidden="true" required value="" />
</form>
```


<div class="field">
    <input type="text">
</div>

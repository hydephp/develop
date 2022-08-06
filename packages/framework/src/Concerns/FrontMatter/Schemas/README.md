# Working draft for front matter schema documentation

## What front matter schema traits are

The schema traits define the public API used for front matter.
Each supported front matter setting will have a corresponding class property in the appropriate schema trait.
For example, blog post data will be in the BlogPostSchema trait.

The names of the properties should match the names of the front matter settings.
For example, the `title` property will be the `title` front matter setting.

## What they're not

Schemas may have constructors to assign data dynamically, but they should not include any data that cannot be entered with front matter.
For example, the sidebar table of contents for documentation pages should not be in a schema as that cannot be changed with front matter.
However, the actual sidebar label can be changed with front matter and is thus in the schema. If a label is not set in the front matter,
the schema constructor will assign an appropriate one.

## Data types
The data types used for the class properties should match the ones in the front matter, and if they don't, the actual YAML values should be documented.

For example, the blog post author model can in the YAML matter be a string or array, but internally it is always an Author object or null. So the following is used instead:
    
```php
/* @yamlType string|array|optional */
public ?Author $author = null;
```

In the following example, the internal data type matches the YAML type, and thus does not require a `@yamlType` annotation:
    
```php
public ?string $category;
```

Nullable types mean that the property is optional. Actually setting the value in null with the front matter may lead to unexpected behavior. 
Instead, the nullable type designates that the property can safely be omitted.

# Working draft for front matter schema documentation

The schema traits define the public API used for front matter.
Each supported front matter setting will have a corresponding class property in the appropriate schema trait.
For example, blog post data will be in the BlogPostSchema trait.

The names of the properties should match the names of the front matter settings.
For example, the `title` property will be the `title` front matter setting.

Schemas may have constructors to assign data dynamically, but they should not include any data that cannot be entered with front matter.
For example, the sidebar table of contents for documentation pages should not be in a schema as that cannot be changed with front matter.
However, the actual sidebar label can be changed with front matter and is thus in the schema. If a label is not set in the front matter,
the schema constructor will assign an appropriate one.

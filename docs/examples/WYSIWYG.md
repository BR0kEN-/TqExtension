# Testing WYSIWYG

Scenarios, which will use steps for testing WYSIWYG editors, should be tagged with `@wysiwyg` tag.

**Note**: For now, the `WysiwygContext` works only with [CKEditor](http://ckeditor.com). Support
of the [TinyMCE](http://www.tinymce.com) planed in future.  

```gherkin
@wysiwyg
Scenario: Testing WYSIWYG
  Given I am logged in as a user with "administrator" role
  Then I am at "node/add/employer"
  And work with "Career" WYSIWYG editor
  And fill "<strong>Additional</strong>" in WYSIWYG editor
  And type " information" in WYSIWYG editor
  And should see "information" in WYSIWYG editor
  And should not see "vulnerability" in WYSIWYG editor
  Then I work with "Experience" WYSIWYG editor
  And fill "<strong>My awesome experience</strong><ul><li>Knowledge</li></ul>" in WYSIWYG editor
  And should see "awesome" in WYSIWYG editor
  Then fill "<p>text</p>" in "Education" WYSIWYG editor
```

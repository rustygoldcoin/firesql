# Release Changes

* 3.1.0
    * Update FireSql to accept new option that will allow you to set a classname for the model so that all objects of a collection are returned as that object.
    * Added type checking when inserting or updating objects in a collection that has a model type set.
* 3.0.1
    * Update the render method logic in the sql debug panel to use $this->name instead of self::NAME.
    * Added description to the firesql debug panel.
* 3.0.0
    * Uplifting code to be in line with UA1 Labs 2.0 standards.
    * Added test cases.

**Steps To Create Release**

1. Add version changes to `RELEASE.md`.
2. Update release version in `composer.json`.
3. Merge changes to master branch and push master branch changes upstream.
4. Create git tag with release version: `git tag X.X.X`
5. Push new git tag upstream.

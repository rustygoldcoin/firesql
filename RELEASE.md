# Release Changes
* 2.1.0
    * Adding documentation for source code and README.
* 2.0.1
    * Update Collection::update() method to return the updated object so a client knows when an object is truely updated.
* 2.0.0
    * Change database table structure so that a collection has its own set of tables and all collections do not continue to share the same two tables.
* 1.4.1
    * Update collection->find() so that when it is called with a objectId is only looks for the object within the collection and not all objects in the database.
* 1.4.0
    * Change the default behavior of upsertion. Currently, the default behavior is to track the history of any object in a collection. This change will update this functionality to make it an option rather than the default behavior. The new default behavior wil be to update any existing objects and not keep track of the history of an object.
* 1.3.1
    * Update sql queries to fix issues with newer version of mysql.
* 1.3.0
    * Add ability to pass in filter into collection->count() to get object count for the filter.
* 1.2.0
    * Add count method to collection that will return the number of objects in a collection.
    * Update firebug panel to use the new firebug render helpers.
* 1.1.1
    * Update firesql.phtml to use the new renderTrace() available in the Fire\Bug\Panel object.
    * Remove composer.lock as it is not needed because this is a library.
    * Adjust upsert process to commit object using version as well as id.
* 1.1.0
    * Remove hard coded styles for hr in firesql.phtml
    * Add count to the FireSql panel in firebug. It should read {2} FireSql
* 1.0.0
    * Initial Release

**Steps To Create Release**

1. Make sure all code is commented out in `demo/addRecords.php` and `demo/getRecords.php`.
1. Add version changes to `RELEASE.md`.
2. Update release version in `composer.json`.
3. Merge changes to master branch and push master branch changes upstream.
4. Create git tag with release version: `git tag X.X.X`
5. Push new git tag upstream.

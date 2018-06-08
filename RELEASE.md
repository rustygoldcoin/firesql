# Release Changes

* 1.0.0
    * Initial Release

**Steps To Create Release**

1. Make sure all code is commented out in `demo/addRecords.php` and `demo/getRecords.php`.
1. Add version changes to `RELEASE.md`.
2. Update release version in `composer.json`.
3. Merge changes to master branch and push master branch changes upstream.
4. Create git tag with release version: `git tag X.X.X`
5. Push new git tag upstream.

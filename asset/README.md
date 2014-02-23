Apigility Admin UI
==================

This is the source code for the Apigility Admin user interface.

Requirements
------------

- [npm](https://npmjs.org/), for installing the various development
  requirements, which primarily includes [Grunt](http://gruntjs.com) and
  [Bower](http://bower.io/), and tools these to utilize.

Run the following command from this directory to install dependencies:

```sh
npm install
```

If you have not yet installed Bower, please do so:

```sh
sudo npm install -g bower
```

Finally, invoke Bower to install the relevant CSS and JS libraries:

```sh
bower install
```

Workflow
--------

All changes to the admin UI code should be made in the `src` directory. You have
two options for compiling resources to the `dist` directory:

- Run `grunt build` manually from this directory or any subdirectory.
- Run `grunt watch` from this directory or any subdirectory; this will pick up
  any edits you make and run the appropriate build tasks. If it fails, it will 
  notify you with the errors.

When you are satisfied with your changes, be sure to commit both the `src` and
`dist` files.

Adding JS/CSS Dependencies
--------------------------

If you need to add any new JS or CSS dependencies, please do so as follows:

- Edit the `bower.json` file and add the dependency
- Execute `bower install`
- Execute `git add vendor`
- Commit your changes

At this point, you should add the necessary scripts to the relevant build tasks
in the `Gruntfile.js`. Typically:

- For CSS, update the `cssmin.minify.files` array to add the appropriate CSS
  files. Make sure you add the non-minified variants!
- For JS, update the `src` key under the appropriate heading below the `concat`
  key. E.g: if you are adding UI-related JS, put it in `concat.vendorUi.src`;
  for general utility JS, put it in `concat.vendorUtil.src`; for Angular
  modules, put it in `concat.vendorAngular.src`.

Occasionally, you will find that either a CSS library or JS script relies on
assets installed via the vendor. You can copy these to the correct locations
under the `copy` heading; use the Bootstrap Glyphicon fonts and the Select2
widget as examples.


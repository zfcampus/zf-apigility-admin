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
in the `Gruntfile.js`. If you look under the "concat" section, you will see
existing configuration for CSS as well as UI, util, and Angular JS. Adding them
here will allow grunt to concatenate and minify the files.

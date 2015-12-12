..

  **This project is now DEPRECATED. Currently there are better alternatives, please use those in new projects.**


-----------------------------
GlorpenCompassConnectorBundle
-----------------------------

The better Compass integration for Symfony2.

For forking and other funnies:

- https://bitbucket.org/glorpen/glorpencompassconnectorbundle
- https://github.com/glorpen/GlorpenCompassConnectorBundle

What problems is it solving?
============================

This bundle:

- adds bundle namespace for compass files - so you can do cross bundle imports or use assets from other bundle

  - ... and it should enable distributing bundles with compass assets

- you don't need installed assets in ``your_app/web`` - connector uses files from eg. ``SomeBundle/Resources`` dir
- assets recompiling/updating when any of its dependencies are modified - be it another import, inlined font file or just ``width: image-width(@SomeBundle:public/myimage.png);``
- for referencing files inside ``app/Resources`` dir use just ``@somefile.png`` (sprites, inline images, scss imports)

How to install
==============

- first, you need to install ruby connector gem:

.. sourcecode:: bash

   gem install compass-connector

- add requirements to composer.json:

.. sourcecode:: json

   {
       "require": {
           "glorpen/compass-connector-bundle": "*"
       }
   }
   

- enable the bundle in your **AppKernel** class

*app/AppKernel.php*

.. sourcecode:: php

    <?php
    
    class AppKernel extends AppKernel
    {
       public function registerBundles()
       {
           $bundles = array(
               ...
               new Glorpen\Assetic\CompassConnectorBundle\GlorpenCompassConnectorBundle(),
               ...
           );
       }
    }

- add assetic filter config in config.yml

.. sourcecode:: yaml

   assetic:
       filters:
           compass_connector:
              resource: "%kernel.root_dir%/../vendor/glorpen/compass-connector-bundle/Glorpen/Assetic/CompassConnectorBundle/Resources/config/filter.xml"
              #apply_to: ".scss$" # uncomment to auto-apply to all scss assets


If you are running into following error:

*Scope Widening Injection detected: The definition "assetic.filter.compass_connector.resolver" references the service "templating.asset.default_package"
which belongs to a narrower scope. Generally, it is safer to either move "assetic.filter.compass_connector.resolver" to scope "request" or alternatively
rely on the provider pattern by injecting the container itself, and requesting the service "templating.asset.default_package" each time it is needed.
In rare, special cases however that might not be necessary, then you can set the reference to strict=false to get rid of this error.*

... or if you just want to change generated assets url (for eg. CDN).

You need to add following configuration to you project (remember to change urls):

.. sourcecode:: yaml

   framework:
      templating:
         assets_base_urls:
            http: ["http://localhost:8000"]
            ssl: ["http://localhost:8000"]


Usage
=====

There are five kind of "paths":

- app: looks like ``@MyBundle:public/images/asset.png``
- app global: cannot be converted to URL, looks like ``@data/image.png`` and will resolve to ``app/Resources/data/image.png``
- absolute: starts with single ``/``, should be publicly available, will resolve to ``web/``
- vendor: a relative path, should be used only by compass plugins (eg. zurb-foundation, blueprint)
- absolute path: starts with ``/``, ``http://`` etc. and will NOT be changed by connector

Some examples:

.. sourcecode:: css

   @import "@SomeBundle:scss/settings"; /* will resolve to src/SomeBundle/Resources/scss/_settings.scss */
   @import "foundation"; /* will include foundation scss from your compass instalation */
   
   width: image-size("@SomeBundle:public/images/my.png"); /* will output image size of SomeBundle/Resources/public/images/my.png */
   background-image: image-url("@SomeBundle:public/images/my.png"); /* will generate url with prefixes given by Symfony2 config */
   @import "@SomeBundle:sprites/*.png"; /* will import sprites located in src/SomeBundle/Resources/sprites/ */


This bundle uses Assetic and CompassConnector filter name is ``compass_connector``.

Confguration
============

You can change default configuration by setting following DIC parameters:

.. sourcecode:: yaml

   parameters:
      assetic.filter.compass_connector.plugins:
         "zurb-foundation": ">4"
      assetic.filter.compass_connector.imports: ["/some/path"]
      assetic.filter.compass_connector.cache_path: %kernel.root_dir%/cache/compassConnector
      assetic.filter.compass_connector.compass_bin: /usr/bin/compass
      assetic.filter.compass_connector.resolver.output_dir: %kernel.root_dir%/../web/compass
      assetic.filter.compass_connector.resolver.vendor_prefix: vendors
   
As for `assetic.filter.compass_connector.plugins` you can provide arguments as a list eg. `["zurb-foundation"]` or array with required gem version: `{"zurb-foundation":">=4"}`

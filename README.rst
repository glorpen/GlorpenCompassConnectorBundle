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

Usage
=====

There are three kind of "paths":

- app: looks like ``@MyBundle:public/images/asset.png``
- vendor: a relative path, should be used only by compass plugins (eg. zurb-foundation, blueprint)
- absolute path: starts with ``/``, ``http://`` etc. and will NOT be changed by connector

Some examples:

.. sourcecode:: css

   @import "@SomeBundle:scss/settings"; /* will resolve to src/SomeBundle/Resources/scss/_settings.scss */
   @import "foundation"; /* will include foundation scss from your compass instalation */
   
   width: image-size("@SomeBundle:public/images/my.png"); // will output image size of SomeBundle/Resources/public/images/my.png
   background-image: image-url("@SomeBundle:public/images/my.png"); // will generate url with prefixes given by Symfony2 config
   @import "@SomeBundle:sprites/*.png"; // will import sprites located in src/SomeBundle/Resources/sprites/


This bundle uses Assetic and its filter name is ``compass_connector``.


-----------------------------
GlorpenCompassConnectorBundle
-----------------------------

Provides Compass integration with Symfony2 project and possibly any other PHP framework - bundle could be used as standalone assetic filter.

How to install
==============

- first, you need to install connector for ruby side:

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
               new Glorpen\CompassConnectorBundle\GlorpenCompassConnectorBundle(),
               ...
           );
       }
    }


Configuration
=============

Constructor:

- cache_path

   **parameter**: *assetic.filter.compass_connector.cache_path*
   
   **default value**: `%kernel.root_dir%/cache/compassConnector`
   
- compass_bin

   **parameter**: *assetic.filter.compass_connector.compass_bin*
   
   **default value**: `/usr/bin/compass`

- connector_class

   **parameter**: *assetic.filter.compass_connector.connector_class*
   
   **default value**: `Glorpen\CompassConnectorBundle\Connector\Symfony2Connector`

Methods:

- setPlugins

   **parameter**: *assetic.filter.compass_connector.plugins*
   
   **default value**: `array()`
   
- setVendorsPath
   
   **parameter**: *assetic.filter.compass_connector.vendors.path*
   
   **default value**: `%kernel.root_dir%/../web/vendors`
   
- setVendorsWeb

   **parameter**: *assetic.filter.compass_connector.vendors.web*
   
   **default value**: `/vendors`
   
- setGeneratedImagesPath

   **parameter**: *assetic.filter.compass_connector.generated_images.path*
   
   **default value**: `%kernel.root_dir%/../web/assetic/generated-images`
   
- setGeneratedImagesWeb

   **parameter**: *assetic.filter.compass_connector.generated_images.web*
   
   **default value**: `/assetic/generated-images`
   
- setEnvironment

   **parameter**: *assetic.filter.compass_connector.environment*
   
   **default value**: `development`
   
- setSassRoot

   **parameter**: *assetic.filter.compass_connector.sass_root*
   
   **default value**: `%kernel.root_dir%/../`



Usage
=====

SCSS files should be placed in *SomeBundle/Resources/scss*.

.. sourcecode:: css

   @import "SomeBundle:settings"; /* will resolve to eg. .../SomeBundle/Resources/scss/_settings.scss */
   @import "foundation"; /* will include foundation scss from your compass instalation */
   
   image-size("/bundles/some/images/my.png");
   image-url("/bundles/some/images/my.png");
   /* paths with /bundles/<bundlename>/ will resolve to Resources/public in respective bundles */


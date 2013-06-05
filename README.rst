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
              #apply_to: ".scss$"

Configuration
=============

   **parameter**: *assetic.filter.compass_connector.compass_bin*
   
   **default value**: `/usr/bin/compass`

TODO

Usage
=====

.. sourcecode:: css

   @import "SomeBundle:scss/settings"; /* will resolve to eg. .../SomeBundle/Resources/scss/_settings.scss */
   @import "foundation"; /* will include foundation scss from your compass instalation */
   
   image-size("@SomeBundle:images/my.png");
   image-url("@SomeBundle:images/my.png");
   /* paths will resolve to Resources/public/... in respective bundles */


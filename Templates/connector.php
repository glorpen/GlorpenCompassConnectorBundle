<?php

CLASS_FILEPATHS;

use CONNECTOR_CLASS as Connector;

$c = new Connector(CONNECTOR_CONFIG);
$c->initialize();
$c->execute();

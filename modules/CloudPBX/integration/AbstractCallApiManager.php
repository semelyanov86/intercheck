<?php
namespace CloudPBX\integration;
abstract class AbstractCallApiManager {
    public abstract function doOutgoingCall($number);
}
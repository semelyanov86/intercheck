<?php
namespace CloudPBX\domru\notifications;

use CloudPBX\gravitel\notifications\GravitelHistoryNotification;

class DomruHistoryNotification extends GravitelHistoryNotification {
    use GravitelAdapterTrait;    
}
<?php

namespace CloudPBX\domru\notifications;

use CloudPBX\gravitel\notifications\GravitelEventNotification;

class DomruEventNotification extends GravitelEventNotification {        
    use GravitelAdapterTrait;
}

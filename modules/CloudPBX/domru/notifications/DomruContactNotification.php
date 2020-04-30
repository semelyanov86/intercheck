<?php
namespace CloudPBX\domru\notifications;

use CloudPBX\gravitel\notifications\GravitelContactNotification;

class DomruContactNotification extends GravitelContactNotification {
    use GravitelAdapterTrait;
}

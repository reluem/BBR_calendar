<?php
    
    use reluem\processFormData;
    
    $GLOBALS['TL_HOOKS'][\Codefog\EventsSubscriptions\EventDispatcher::EVENT_ON_SUBSCRIBE][] = [
        processFormData::class,
        'onSubscribe',
    ];
<?php
    
    
    
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['events_subscriptions']['events_subscriptions_subscribe']['attachment_tokens'] = ['ics_file'];
    
    \Codefog\EventsSubscriptions\Services::getSubscriptionFactory()->add(
        'guest', \Reluem\BBRCalendarBundle\guestSubscriptionExt::class
    );

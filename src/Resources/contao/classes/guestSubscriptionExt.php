<?php
    
    
    namespace Reluem\BBRCalendarBundle;
    
    use Codefog\EventsSubscriptions\Event\SubscribeEvent;
    use Codefog\EventsSubscriptions\Subscription\GuestSubscription;
    use Codefog\EventsSubscriptions\Subscription\SubscriptionInterface;
    use Contao\BackendTemplate;
    use Contao\CalendarEventsModel;
    use Contao\Environment;
    use Contao\Events;
    use Contao\Input;
    use Eluceo\iCal\Component\Calendar;
    use Eluceo\iCal\Component\Event;
    use Patchwork\Utf8;
    
    
    class guestSubscriptionExt extends guestSubscription
    {
        
        public function getNotificationTokens()
        {
            dump($this->subscriptionModel);
            dump($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']);
            $buffer[] = ['ics_file' => $this->generateIcsFile()];
            dump($buffer);
            count(l);
        }
        
        /**
         * On subscribe to event
         *
         * @param SubscribeEvent $event
         */
        public function onSubscribe(SubscribeEvent $event)
        {
            $arrData = $event->getModel();
            $objEvent = \CalendarEventsModel::findOneBy('id', $arrData->pid);
            if ($objEvent) {
                $this->generateIcsFile($objEvent);
            }
        }
        
        
        public function generateIcsFile(CalendarEventsModel $objEvent)
        {
            $vCalendar = new Calendar(Environment::get('url'));
            $vEvent = new Event();
            $noTime = false;
            if ($objEvent->startTime === $objEvent->startDate && $objEvent->endTime === $objEvent->endDate) {
                $noTime = true;
            }
            $vEvent
                ->setDtStart(\DateTime::createFromFormat('d.m.Y - H:i:s',
                    date('d.m.Y - H:i:s', (int)$objEvent->startTime)))
                ->setDtEnd(\DateTime::createFromFormat('d.m.Y - H:i:s', date('d.m.Y - H:i:s', (int)$objEvent->endTime)))
                ->setSummary(strip_tags(\Controller::replaceInsertTags($objEvent->title)))
                ->setUseUtc(false)
                ->setLocation($objEvent->location)
                ->setUrl($objEvent->url)
                ->setNoTime($noTime);
            
            $vCalendar->addComponent($vEvent);
            $ics = $vCalendar->render();
            if ($ics) {
                $objFile = new \File('web/share/' . $objEvent->alias . '.ics');
                $objFile->write($ics);
                $objFile->close();
            }
        }
        
    }
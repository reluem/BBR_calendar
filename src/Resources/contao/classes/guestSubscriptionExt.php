<?php
    
    
    namespace Reluem\BBRCalendarBundle;
    
    use Codefog\EventsSubscriptions\Event\SubscribeEvent;
    use Codefog\EventsSubscriptions\Subscription\GuestSubscription;
    use Codefog\EventsSubscriptions\Subscription\SubscriptionInterface;
    use Contao\BackendTemplate;
    use Contao\CalendarEventsModel;
    use Contao\Environment;
    use Contao\System;
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
            $objEvent = \CalendarEventsModel::findOneBy('id', $this->subscriptionModel->pid);
            $buffer = parent::getNotificationTokens();
            $buffer['ics_file'] = $this->generateIcsFile($objEvent);
            return $buffer;
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
                return $objFile->path;
            } else {
                return null;
            }
        }
        
    }
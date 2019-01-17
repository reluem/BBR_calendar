<?php
    
    
    namespace reluem;
    
    use Codefog\EventsSubscriptions\Event\SubscribeEvent;
    use Contao\CalendarEventsModel;
    use Eluceo\iCal\Component\Calendar;
    use Eluceo\iCal\Component\Event;
    
    
    class processFormData
    {
        
        
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
        
        
        public function generateIcsFile(CalendarEventsModel $objEvent): void
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
                ->setSummary(strip_tags($this->replaceInsertTags($objEvent->title)))
                ->setUseUtc(false)
                ->setLocation($objEvent->location)
                ->setNoTime($noTime);
            // HOOK: modify the vEvent
            if (isset($GLOBALS['TL_HOOKS']['modifyIcsFile']) && is_array($GLOBALS['TL_HOOKS']['modifyIcsFile'])) {
                foreach ($GLOBALS['TL_HOOKS']['modifyIcsFile'] as $callback) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($vEvent, $objEvent, $this);
                }
            }
            $vCalendar->addComponent($vEvent);
            header('Content-Type: text/calendar; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $objEvent->alias . '.ics"');
            echo $vCalendar->render();
            exit;
        }
        
    }
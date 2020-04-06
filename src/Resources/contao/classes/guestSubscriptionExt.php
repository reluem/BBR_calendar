<?php
    
    namespace Reluem\BBRCalendarBundle;
    
    use Codefog\EventsSubscriptions\Event\SubscribeEvent;
    use Codefog\EventsSubscriptions\Model\SubscriptionModel;
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
    use Eluceo\iCal\Property\Event\Organizer;
    use Patchwork\Utf8;
    use \Haste\Util\StringUtil;
    
    
    class guestSubscriptionExt extends guestSubscription
    {
        
        public function getNotificationTokens()
        {
            $objEvent = \CalendarEventsModel::findOneBy('id', $this->subscriptionModel->pid);
            $buffer = parent::getNotificationTokens();
            // if .ICS should be added to notification, call method
            if ($objEvent->addICS) {
                $buffer['ics_file'] = $this->generateIcsFile($objEvent, $this->subscriptionModel);
            }
            return $buffer;
            
        }
        
        
        public function generateIcsFile(CalendarEventsModel $objEvent, SubscriptionModel $objSubscription): string
        {
            $vCalendar = new Calendar(Environment::get('url'));
            $vCalendar->setMethod('REQUEST');
            $vEvent = new Event();
            $noTime = false;
            if ($objEvent->startTime === $objEvent->startDate && $objEvent->endTime === $objEvent->endDate) {
                $noTime = true;
            }
            $desc = StringUtil::convertToText($objEvent->teaser, StringUtil::NO_TAGS);
            if ($objEvent->eventAttendanceMode === 'OnlineEventAttendanceMode' && $objEvent->onlineDescription) {
                $desc .= StringUtil::convertToText($objEvent->onlineDescription, StringUtil::NO_TAGS);
            }
            $location = ($objEvent->location ?: '') . (($objEvent->location && $objEvent->address) ? ', ' : '') . ($objEvent->address ?: '');
            $vEvent
                ->setDtStart(\DateTime::createFromFormat('d.m.Y - H:i:s',
                    date('d.m.Y - H:i:s', (int)$objEvent->startTime)))
                ->setDtEnd(\DateTime::createFromFormat('d.m.Y - H:i:s', date('d.m.Y - H:i:s', (int)$objEvent->endTime)))
                ->setDtStamp(\DateTime::createFromFormat('d.m.Y - H:i:s',
                    date('d.m.Y - H:i:s', (int)$objEvent->tstamp)))
                ->setSummary(strip_tags(\Controller::replaceInsertTags($objEvent->title)))
                ->setDescription($desc)
                ->setUseUtc(false)
                ->setOrganizer(new Organizer('MAILTO:c.rech@bbr-consulting.com',
                    ['CN' => 'Christian Rech']))
                ->addAttendee('MAILTO:' . $objSubscription->email,
                    [
                        'CUTYPE' => 'INDIVIDUAL',
                        'ROLE' => 'REQ-PARTICIPANT',
                        'PARTSTAT' => 'NEEDS-ACTION',
                        'RSVP' => 'TRUE',
                        'CN' => $objSubscription->firstname . ' ' . $objSubscription->lastname,
                    ])
                ->setLocation($location)
                ->setUrl($objEvent->onlineURL)
                ->setNoTime($noTime);
            
            $vCalendar->addComponent($vEvent);
            $ics = $vCalendar->render();
            if ($ics) {
                $objFile = new \File('system/tmp/invite_' . $objEvent->id . '.ics');
                $objFile->write($ics);
                $objFile->close();
                return $objFile->path;
            } else {
                return null;
            }
        }
        
    }
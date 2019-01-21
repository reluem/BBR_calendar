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
    
    
    class guestSubscriptionExt extends guestSubscription
    {
        
        public function getNotificationTokens()
        {
            $objEvent = \CalendarEventsModel::findOneBy('id', $this->subscriptionModel->pid);
            $buffer = parent::getNotificationTokens();
            $buffer['ics_file'] = $this->generateIcsFile($objEvent, $this->subscriptionModel);
            return $buffer;
        }
        
        
        public function generateIcsFile(CalendarEventsModel $objEvent, SubscriptionModel $objSubscription)
        {
            $vCalendar = new Calendar(Environment::get('url'));
            $vCalendar->setMethod('REQUEST');
            $vEvent = new Event();
            $noTime = false;
            if ($objEvent->startTime === $objEvent->startDate && $objEvent->endTime === $objEvent->endDate) {
                $noTime = true;
            }
            $vEvent
                ->setDtStart(\DateTime::createFromFormat('d.m.Y - H:i:s',
                    date('d.m.Y - H:i:s', (int)$objEvent->startTime)))
                ->setDtEnd(\DateTime::createFromFormat('d.m.Y - H:i:s', date('d.m.Y - H:i:s', (int)$objEvent->endTime)))
                ->setDtStamp(\DateTime::createFromFormat('d.m.Y - H:i:s',
                    date('d.m.Y - H:i:s', (int)$objEvent->tstamp)))
                ->setSummary(strip_tags(\Controller::replaceInsertTags($objEvent->title)))
                ->setDescription(strip_tags(\Controller::replaceInsertTags($objEvent->title)) . "\n\nNehmen Sie an dem Meeting per Computer, Tablet oder Smartphone teil.\n" . $objEvent->url . "\n\nZum ersten Mal bei GoToMeeting? Hier können Sie eine Systemprüfung durchführen: https://link.gotomeeting.com/system-check")
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
                ->setLocation($objEvent->location)
                ->setUrl($objEvent->url)
                ->setNoTime($noTime);
            
            $vCalendar->addComponent($vEvent);
            $ics = $vCalendar->render();
            if ($ics) {
                $objFile = new \File('web/share/invite_' . $objEvent->id . '.ics');
                $objFile->write($ics);
                $objFile->close();
                return $objFile->path;
            } else {
                return null;
            }
        }
        
    }
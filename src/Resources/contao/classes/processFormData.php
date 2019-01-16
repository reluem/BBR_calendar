<?php
    
    
    namespace reluem;
    
    use Contao\Backend;
    
    class processFormData extends Backend
    {
        
        public function myProcessFormData($arrPost, $arrForm, $arrFiles)
        {
            dump($this);
            dump($arrPost);
            
            // 3. Echo out the ics file's contents
            $ics = "BEGIN:VCALENDAR\r\n";
            $ics .= "VERSION:2.0\r\n";
            $ics .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
            $ics .= "CALSCALE:GREGORIAN\r\n";
            // Timezone-settings borrowed from
            // http://pcal.gedaechtniskirche.com/termine/index.php?cal=kwg-probenplan&ics
            $ics .= "BEGIN:VTIMEZONE\r\n";
            $ics .= "TZID:Europe/Berlin\r\n";
            $ics .= "BEGIN:DAYLIGHT\r\n";
            $ics .= "TZOFFSETFROM:+0100\r\n";
            $ics .= "DTSTART:19810329T020000\r\n";
            $ics .= "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\r\n";
            $ics .= "TZNAME:MESZ\r\n";
            $ics .= "END:DAYLIGHT\r\n";
            $ics .= "BEGIN:STANDARD\r\n";
            $ics .= "TZOFFSETFROM:+0200\r\n";
            $ics .= "DTSTART:19961027T030000\r\n";
            $ics .= "RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\r\n";
            $ics .= "TZNAME:MEZ\r\n";
            $ics .= "END:STANDARD\r\n";
            $ics .= "END:VTIMEZONE\r\n";
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "DTEND;TZID=Europe/Berlin:" . date('Ymd\THis\Z', $this->dateEnd) . "\r\n";
            $ics .= "UID:" . $this->eventId . "\r\n";
            $ics .= "DTSTAMP:TZID=Europe/Berlin:" . date('Ymd\THis\Z', $this->dateStart) . "\r\n";
            $ics .= "DTSTART;TZID=Europe/Berlin:" . date('Ymd\THis\Z', $this->dateStart) . "\r\n";
            $ics .= "END:VEVENT\r\n";
            $ics .= "END:VCALENDAR\r\n";
            
            dump($ics);
            
            /* XML schreiben */
            if ($ics) {
                
                $filename = 'export-' . time(); //export-1420066800
                $path = 'export/' . $filename . '.ics'; //export/export-1420066800.ics
                
                if (!file_exists($path)) {
                    file_put_contents($path, $ics); //Datei schreiben
                }
                
            }
        }
        
    }
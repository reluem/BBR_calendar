<?php
    
    use Contao\CoreBundle\DataContainer\PaletteManipulator;
    
    $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['__selector__'][] = 'eventAttendanceMode';
    
    $GLOBALS['TL_DCA']['tl_calendar_events']['subpalettes']['eventAttendanceMode_OnlineEventAttendanceMode'] = 'onlineURL, onlineDescription';
    
    
    PaletteManipulator::create()
        ->addField("addICS", "enclosure_legend", PaletteManipulator::POSITION_APPEND)
        ->addField("eventAttendanceMode", "teaser", PaletteManipulator::POSITION_AFTER)
        ->applyToPalette("default", "tl_calendar_events");
    
    /**
     * Fields
     */
    
    
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['addICS'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_calendar_events']['addICS'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'sql' => "char(1) NOT NULL default ''",
    ];
    
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['eventAttendanceMode'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_calendar_events']['eventAttendanceMode'],
        'exclude' => true,
        'inputType' => 'select',
        'options' => $GLOBALS['TL_LANG']['tl_calendar_events']['eventAttendanceMode']['options'],
        'eval' => [
            'includeBlankOption' => false,
            'tl_class' => 'w50',
            'submitOnChange' => true,
        ],
        'sql' => "varchar(255) NOT NULL default ''",
    ];
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['onlineURL'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_calendar_events']['onlineURL'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => [
            'rgxp' => 'url',
            'decodeEntities' => true,
            'maxlength' => 255,
            'fieldType' => 'radio',
            'filesOnly' => true,
            'tl_class' => 'w50 wizard',
            'dcaPicker' => true,
        ],
        'sql' => "varchar(255) NOT NULL default ''",
    ];
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['onlineDescription'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_calendar_events']['onlineDescription'],
        'exclude' => true,
        'search' => true,
        'inputType' => 'textarea',
        'eval' => [
            'rte' => 'tinyMCE',
            'tl_class' => 'clr',
        ],
        'sql' => "text NULL",
    ];
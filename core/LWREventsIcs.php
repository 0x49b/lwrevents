<?php

/**
 * User:        u210645
 * Date:        29.08.2016
 * Time:        14:12
 * File:        LWREventsIcs.php
 * Project:     wordpress
 * Version:     0.1
 * Description:
 */
class LWREventsIcs {

    var $data;
    var $name;

    function __construct($start,$end,$name,$description,$location) {
        $this->name = $name;
        $this->data = "BEGIN:VCALENDAR\nVERSION:2.0\nMETHOD:PUBLISH\nBEGIN:VEVENT\nDTSTART:".date("Ymd\THis\Z",strtotime($start))."\nDTEND:".date("Ymd\THis\Z",strtotime($end))."\nLOCATION:".$location."\nTRANSP: OPAQUE\nSEQUENCE:0\nUID:\nDTSTAMP:".date("Ymd\THis\Z")."\nSUMMARY:".$name."\nDESCRIPTION:".$description."\nPRIORITY:1\nCLASS:PUBLIC\nBEGIN:VALARM\nTRIGGER:-PT10080M\nACTION:DISPLAY\nDESCRIPTION:Reminder\nEND:VALARM\nEND:VEVENT\nEND:VCALENDAR\n";
    }
    function ics_save() {
        file_put_contents($this->name.".ics",$this->data);
    }

    function ics_download(){
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename='".$this->name.".ics'");
        echo readfile($this->name.'.ics');
    }

    function ics_show() {
        header("Content-type:text/calendar");
        header('Content-Disposition: attachment; filename="'.$this->name.'.ics"');
        header('Content-Length: '.strlen($this->data));
        header('Connection: close');
        echo $this->data;
    }
}
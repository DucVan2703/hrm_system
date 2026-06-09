<?php

class TimekeepingController extends BaseController
{
    public function index()
    {
        Helper::requireHR();
        $controller = new AttendanceController();
        $controller->manage('timekeeping', false);
    }
}

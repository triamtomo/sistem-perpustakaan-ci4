<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function belajar_segment()
    {
        $uri = service('uri');
        $parameter1 = $uri->getSegment(3);
        $parameter2 = $uri->getSegment(4);
        $parameter3 = $uri->getSegment(5);

        $data['p1'] = $parameter1;
        $data['p2'] = $parameter2;
        $data['p3'] = $parameter3;

        return view('segment_view', $data);
    }
}
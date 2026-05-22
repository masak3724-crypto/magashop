<?php

namespace App\Http\Controllers;

class ServiceController extends Controller
{
    public function delivery()
    {
        return view('shop.services.delivery');
    }

    public function guarantee()
    {
        return view('shop.services.guarantee');
    }

    public function loyalty()
    {
        return view('shop.services.loyalty');
    }
}

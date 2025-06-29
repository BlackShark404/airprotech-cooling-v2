<?php

namespace App\Controllers;

class HomeController extends BaseController{
    public function index() {
        $this->render('home/index');
    }

    public function renderTest() {
        $this->render('home/test');
    }

    public function service() {
        $this->render('home/service');
    }

    public function products() {
        $this->render('home/products');
    }

    public function contactUs() {
        $this->render('home/contact-us');
    }

    public function privacy() {
        $this->render('home/privacy');
    }

    public function terms() {
        $this->render('home/terms-of-services');
    }
}
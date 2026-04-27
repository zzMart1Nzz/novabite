<?php

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test email from script (SSL)', function ($msg) {
        $msg->to('sushinelli.info@gmail.com')->subject('Test Script SSL');
    });
    echo "Mail sent successfully\n";
} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}

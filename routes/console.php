<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('saas:open-slots')->dailyAt('01:00')->timezone('Asia/Jakarta')->withoutOverlapping(30);

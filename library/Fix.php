<?php

namespace tools;

class Fix 
{
    const API_URL   = "https://api.telegram.org/bot";
    const TOKEN     = "669836088:AAF6iAztFXzlBff58fAF-ATxfX-P7O0b_m8";
    const  MSG      = 'message';
    const  CBQ      = 'callback_query';

    const LOG_ON    = true;
    
    const LOG_FILE  = '/botdir/data/log.txt';
    const DATA_FILE = '/botdir/data/votes.xml';
    const SQLITE_FILE = '/botdir/data/bot-data.sqlite';
}

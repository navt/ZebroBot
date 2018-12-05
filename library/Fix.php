<?php

namespace tools;

class Fix 
{
    const API_URL   = "https://api.telegram.org/bot";
    const TOKEN     = "ваш токен";
    const  MSG      = 'message';
    const  CBQ      = 'callback_query';

    const LOG_ON    = true;
    
    const LOG_FILE  = '/botdir/data/log.txt';
    const SQLITE_FILE = '/botdir/data/bot-data.sqlite';
}

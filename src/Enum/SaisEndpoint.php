<?php

namespace Survos\SaisBundle\Enum;

enum SaisEndpoint: string
{
    case ACCOUNT_SETUP = 'account_setup';
    case DISPATCH_PROCESS = 'dispatch_process';
    case GET_ACCOUNT_INFO = 'get_account_info';
    case UPLOAD_MEDIA = 'upload_media';
    case GENERATE_THUMBNAIL = 'generate_thumbnail';
}

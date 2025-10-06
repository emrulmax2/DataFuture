<?php 

namespace App\Enums;

enum EsignEventType: string
{
    case SIGN_REQUEST_CREATED = 'sign_request_created';
    case EMAIL_SENT = 'email_sent';
    case EMAIL_READ = 'email_read';
    case VIEWED = 'viewed';
    case LOCATION_VERIFIED = 'location_verified';
    case CONSENTED_TO_ESIGN = 'consented_to_esign';
    case FINALIZED = 'finalized';
    case MODIFIED = 'modified';
    case SIGN_REQUEST_FINALIZED = 'sign_request_finalized';
    case RENAMED = 'renamed';

    public function label(): string
    {
        return match($this) {
            self::SIGN_REQUEST_CREATED => 'Sign Request Created',
            self::EMAIL_SENT => 'Email Sent',
            self::EMAIL_READ => 'Email Read',
            self::VIEWED => 'Viewed',
            self::LOCATION_VERIFIED => 'Location Verified',
            self::CONSENTED_TO_ESIGN => 'Consented to Esign',
            self::FINALIZED => 'Finalized',
            self::MODIFIED => 'Modified',
            self::SIGN_REQUEST_FINALIZED => 'Sign Request Finalized',
            self::RENAMED => 'Renamed',
        };
    }

    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }
}

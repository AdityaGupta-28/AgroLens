<?php

namespace App\Enums;

enum Permission: string
{
    case ViewDashboard = 'view_dashboard';
    case ViewGis = 'view_gis';
    case ManageRegions = 'manage_regions';
    case ManageFarmers = 'manage_farmers';
    case ManageSurveys = 'manage_surveys';
    case ManageUsers = 'manage_users';
    case CollectSurveyData = 'collect_survey_data';
    case ViewApi = 'view_api';

    public function label(): string
    {
        return str_replace('_', ' ', ucwords($this->value, '_'));
    }
}

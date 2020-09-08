<?php

namespace App\Dorcas\Enum;


class PermissionName
{
    const OVERLORD = 'overlord';
    
    const MANAGE_COMPANIES = 'companies.manage';
    const MANAGE_COMPANY = 'company.manage';
    const MANAGE_MEMBERS = 'members.manage';
    const MANAGE_PARTNERS = 'partners.manage';
    const MANAGE_SYSTEM_SETTINGS = 'system_settings.manage';
    const MANAGE_USERS = 'users.manage';
    
    const VIEW_COMPANIES = 'companies.view';
    const VIEW_MEMBERS = 'members.view';
    const VIEW_PARTNERS = 'partners.view';
    const VIEW_SYSTEM_SETTINGS = 'system_settings.view';
    const VIEW_USERS = 'users.view';
}
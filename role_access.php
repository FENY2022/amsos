<?php

if (!function_exists('amsos_role_key')) {
    function amsos_role_key($role)
    {
        $role = strtoupper(trim((string)$role));
        return preg_replace('/[^A-Z0-9]+/', '', $role);
    }
}

if (!function_exists('amsos_office_key')) {
    function amsos_office_key($office)
    {
        $office = strtoupper(trim((string)$office));
        return preg_replace('/[^A-Z0-9]+/', '', $office);
    }
}

if (!function_exists('amsos_is_regional_office')) {
    function amsos_is_regional_office($office)
    {
        $officeKey = amsos_office_key($office);
        return strpos($officeKey, 'REGIONALOFFICE') !== false;
    }
}

if (!function_exists('amsos_is_penro_or_cenro')) {
    function amsos_is_penro_or_cenro($office)
    {
        $officeKey = amsos_office_key($office);
        return strpos($officeKey, 'PENRO') !== false || strpos($officeKey, 'CENRO') !== false;
    }
}

if (!function_exists('amsos_is_valid_chief_role_for_office')) {
    function amsos_is_valid_chief_role_for_office($role, $office)
    {
        $roleKey = amsos_role_key($role);

        if ($roleKey === 'DIVISIONCHIEF') {
            return amsos_is_regional_office($office);
        }

        if ($roleKey === 'SECTIONCHIEF') {
            return amsos_is_penro_or_cenro($office);
        }

        return true;
    }
}

if (!function_exists('amsos_is_limited_operational_role')) {
    function amsos_is_limited_operational_role($role)
    {
        $limitedRoles = [
            'ENCODER',
            'VERIFIER',
            'APPROVER',
            'RECOMMENDINGAPPROVAL',
            'DIVISIONCHIEF',
            'SECTIONCHIEF',
        ];

        return in_array(amsos_role_key($role), $limitedRoles, true);
    }
}

if (!function_exists('amsos_can_manage_configuration')) {
    function amsos_can_manage_configuration($role)
    {
        return !amsos_is_limited_operational_role($role);
    }
}

if (!function_exists('amsos_filter_roles_for_office')) {
    function amsos_filter_roles_for_office(array $roles, $office)
    {
        $filtered = [];

        foreach ($roles as $role) {
            $role = trim((string)$role);
            if ($role === '') {
                continue;
            }

            $roleKey = amsos_role_key($role);

            if ($roleKey === 'DIVISIONCHIEF' && !amsos_is_regional_office($office)) {
                continue;
            }

            if ($roleKey === 'SECTIONCHIEF' && !amsos_is_penro_or_cenro($office)) {
                continue;
            }

            $filtered[$roleKey] = $role;
        }

        if (amsos_is_regional_office($office)) {
            $filtered['DIVISIONCHIEF'] = 'Division Chief';
            unset($filtered['SECTIONCHIEF']);
        } elseif (amsos_is_penro_or_cenro($office)) {
            $filtered['SECTIONCHIEF'] = 'Section Chief';
            unset($filtered['DIVISIONCHIEF']);
        } else {
            unset($filtered['DIVISIONCHIEF'], $filtered['SECTIONCHIEF']);
        }

        uasort($filtered, 'strnatcasecmp');
        return array_values($filtered);
    }
}

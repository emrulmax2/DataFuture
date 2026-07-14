<?php

namespace App\Services;

use App\Models\EmployeePermission;

class PermissionService
{
    /**
     * Check if user has specific permission
     */
    public function checkPermission($user_id, $permission_key)
    {
        $permission = EmployeePermission::where('user_id', $user_id)->where('key', $permission_key)->first();

        if (!$permission) {
            return false;
        }

        return !empty($permission->value) && $permission->value == '1';
    }

    /**
     * Check if user has any of the given permissions
     */
    public function checkAnyPermission($user_id, array $permissions)
    {
        $simplePermissions = [];
        $valuedPermissions = [];

        foreach ($permissions as $key => $value) {
            if (is_int($key)) {
                $simplePermissions[] = $value;
            } else {
                $valuedPermissions[$key] = $value;
            }
        }

        $allKeys = array_merge($simplePermissions, array_keys($valuedPermissions));

        if (empty($allKeys)) {
            return false;
        }

        $userPermissions = EmployeePermission::where('user_id', $user_id)->whereIn('key', $allKeys)->get()->keyBy('key');

        foreach ($simplePermissions as $permissionKey) {
            if (isset($userPermissions[$permissionKey]) && !empty($userPermissions[$permissionKey]->value) && $userPermissions[$permissionKey]->value !== '0') {
                return true;
            }
        }

        foreach ($valuedPermissions as $permissionKey => $expectedValue) {
            if (isset($userPermissions[$permissionKey]) && $userPermissions[$permissionKey]->value === $expectedValue) {
                return true;
            }
        }

        return false;
    }
}
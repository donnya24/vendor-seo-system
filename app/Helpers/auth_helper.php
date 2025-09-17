<?php

use CodeIgniter\Shield\Entities\User;

if (! function_exists('getUserPermissions')) {
    function getUserPermissions(User $user): array
    {
        $authGroups = config('AuthGroups');

        $permissions = [];

        foreach ($user->getGroups() as $group) {
            if (isset($authGroups->matrix[$group])) {
                foreach ($authGroups->matrix[$group] as $p) {
                    // Kalau wildcard (admin.*), expand semua
                    if (str_contains($p, '*')) {
                        $prefix = rtrim($p, '.*');
                        foreach ($authGroups->permissions as $key => $desc) {
                            if (str_starts_with($key, $prefix)) {
                                $permissions[$key] = $desc;
                            }
                        }
                    } else {
                        $permissions[$p] = $authGroups->permissions[$p] ?? $p;
                    }
                }
            }
        }

        return $permissions;
    }
}

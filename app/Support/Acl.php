<?php

namespace App\Support;

class Acl
{
    /** ability => label. Key format: "{policyMethod}:{ModelBasename}". Labels are translatable. */
    public static function labels(): array
    {
        return [
            'viewAny:User' => __('users.ability_view_any'),
            'create:User' => __('users.ability_create'),
            'update:User' => __('users.ability_update'),
            'delete:User' => __('users.ability_delete'),
            'viewAny:Asset' => __('assets.ability_view_any'),
            'create:Asset' => __('assets.ability_create'),
            'update:Asset' => __('assets.ability_update'),
            'delete:Asset' => __('assets.ability_delete'),
            'viewAny:AssetItem' => __('units.ability_view_any'),
            'create:AssetItem' => __('units.ability_create'),
            'update:AssetItem' => __('units.ability_update'),
            'delete:AssetItem' => __('units.ability_delete'),
            'viewAny:Loan' => __('common.ability_loan_view_any'),
            'create:Loan' => __('common.ability_loan_create'),
            'update:Loan' => __('common.ability_loan_update'),
            'delete:Loan' => __('common.ability_loan_delete'),
            'viewAny:Location' => __('locations.ability_view_any'),
            'viewAny:SchoolClass' => __('classes.ability_view_any'),
            'viewAny:Student' => __('students.ability_view_any'),
            'create:Student' => __('students.ability_create'),
        ];
    }


    public static function key(string $ability, mixed $subject): string
    {
        if (is_object($subject)) {
            $subject = $subject::class;
        }

        $name = is_string($subject) ? class_basename($subject) : 'global';

        return $ability.':'.$name;
    }
}

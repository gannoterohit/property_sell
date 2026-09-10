<?php

namespace App\Http\Controllers;

use App\Models\RoomOption;

abstract class Controller
{
    /**
     * Filter a room query by an admin-managed room option group.
     *
     * Accepts either an option id (numeric) or a legacy key (string) and
     * filters the query against the corresponding *_option_id column.
     */
    protected function applyOptionFilter($query, string $group, $value)
    {
        $column = match ($group) {
            'room_type' => 'room_type_option_id',
            'furnishing_type' => 'furnishing_option_id',
            'tenant_type' => 'tenant_option_id',
            default => null,
        };

        if ($column === null) {
            return;
        }

        $values = is_array($value) ? $value : [$value];
        $ids = [];

        foreach ($values as $item) {
            if ($item === null || $item === '') {
                continue;
            }

            if (is_numeric($item)) {
                $ids[] = (int) $item;
                continue;
            }

            $option = RoomOption::active()
                ->where('group', $group)
                ->where('key', (string) $item)
                ->first();

            if ($option) {
                $ids[] = $option->id;
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));

        if (!empty($ids)) {
            $query->whereIn($column, $ids);
        }
    }

    /**
     * Remap validated room-option inputs (keyed by group name, e.g. room_type)
     * to their corresponding *_option_id columns before persisting a Room.
     */
    protected function mapRoomOptionData(array $data): array
    {
        $map = [
            'room_type' => 'room_type_option_id',
            'furnishing_type' => 'furnishing_option_id',
            'tenant_type' => 'tenant_option_id',
        ];

        foreach ($map as $from => $to) {
            if (array_key_exists($from, $data)) {
                $data[$to] = \App\Models\RoomOption::resolveId($from, $data[$from]);
                unset($data[$from]);
            }
        }

        return $data;
    }
}

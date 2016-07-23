<?php


class CalculateAge {
    	public function calculate($timestamp, $comparison = '') {
        $units = array(
            'second' => 60,
            'minute' => 60,
            'hour' => 24,
            'day' => 7,
            'week' => 4.25,
            'month' => 12
        );

        if ($timestamp == 0) {
            return "Never";
        }
        
        if (empty($comparison)) {
            $comparison = $_SERVER['REQUEST_TIME'];
        }
        $age_current_unit = abs($comparison - $timestamp);
        foreach ($units as $unit => $max_current_unit) {
            $age_next_unit = $age_current_unit / $max_current_unit;
            if ($age_next_unit < 1) { // are there enough of the current unit to make one of the next unit?
                $age_current_unit = floor($age_current_unit);
                $formatted_age    = $age_current_unit . ' ' . $unit;
                
                return $formatted_age . ($age_current_unit == 1 ? '' : 's');
            }
            $age_current_unit = $age_next_unit;
        }

        $age_current_unit = round($age_current_unit, 1);
        $formatted_age    = $age_current_unit . ' year';

        return $formatted_age . (floor($age_current_unit) == 1 ? '' : 's');
    }
}